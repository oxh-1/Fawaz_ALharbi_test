/**
 * Admin Handler — Cloudflare Workers + D1
 * Routes: GET /api/admin/users (with last login IP), GET /api/admin/audit-logs,
 *         GET /api/admin/stats, PUT/PATCH/DELETE /api/admin/users/:id
 *
 * ALL endpoints require is_super_admin = true in the JWT payload.
 * The users list includes last_login_ip from audit_logs — visible only to admins.
 */

import { jsonResponse } from '../lib/cors.js';
import { userDb, auditDb, statsDb } from '../lib/db.js';

// ─────────────────────────────────────────────────────────────────────────────
// Guard: admin-only check
// ─────────────────────────────────────────────────────────────────────────────
function requireAdmin(authUser, request) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }
  if (!authUser.is_super_admin) {
    return jsonResponse({ success: false, message: 'Forbidden. Admin access required.' }, 403, request);
  }
  return null; // no error
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/admin/users
// Returns all users including last_login_ip (from audit_logs JOIN)
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminUsers(request, env, authUser) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  const url    = new URL(request.url);
  const search = url.searchParams.get('search') || '';
  const status = url.searchParams.get('status') || '';
  const limit  = parseInt(url.searchParams.get('limit') || '50');
  const offset = parseInt(url.searchParams.get('offset') || '0');

  const users = await userDb.listAll(env.DB, search, status, limit, offset);

  return jsonResponse({
    success: true,
    data: users,
    meta: { limit, offset, count: users.length },
  }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/admin/users/:id
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminGetUser(request, env, authUser, userId) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  const user = await userDb.findById(env.DB, userId);
  if (!user) {
    return jsonResponse({ success: false, message: 'User not found.' }, 404, request);
  }

  return jsonResponse({ success: true, data: user }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// PUT /api/admin/users/:id
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminUpdateUser(request, env, authUser, userId) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const user = await userDb.findById(env.DB, userId);
  if (!user) {
    return jsonResponse({ success: false, message: 'User not found.' }, 404, request);
  }

  const allowed = ['name', 'status', 'is_super_admin'];
  const fields = {};
  for (const key of allowed) {
    if (body[key] !== undefined) fields[key] = body[key];
  }

  if (Object.keys(fields).length === 0) {
    return jsonResponse({ success: false, message: 'No valid fields to update.' }, 422, request);
  }

  await userDb.update(env.DB, userId, fields);

  // Audit log
  await auditDb.create(env.DB, {
    userId:    authUser.sub,
    tenantId:  authUser.tenant_id,
    action:    'admin.update_user',
    model:     'User',
    modelId:   userId,
    oldData:   user,
    newData:   { ...user, ...fields },
    ipAddress: request.headers.get('CF-Connecting-IP') || '',
  });

  const updated = await userDb.findById(env.DB, userId);
  return jsonResponse({ success: true, data: updated }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// PATCH /api/admin/users/:id/status
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminToggleStatus(request, env, authUser, userId) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { status } = body;
  if (!['active', 'inactive', 'banned'].includes(status)) {
    return jsonResponse({ success: false, message: 'Status must be active, inactive, or banned.' }, 422, request);
  }

  const user = await userDb.findById(env.DB, userId);
  if (!user) {
    return jsonResponse({ success: false, message: 'User not found.' }, 404, request);
  }

  if (user.is_super_admin && status === 'banned') {
    return jsonResponse({ success: false, message: 'Cannot ban a super admin.' }, 403, request);
  }

  await userDb.update(env.DB, userId, { status });
  const updated = await userDb.findById(env.DB, userId);
  return jsonResponse({ success: true, data: updated }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// DELETE /api/admin/users/:id
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminDeleteUser(request, env, authUser, userId) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  const user = await userDb.findById(env.DB, userId);
  if (!user) {
    return jsonResponse({ success: false, message: 'User not found.' }, 404, request);
  }

  if (user.is_super_admin) {
    return jsonResponse({ success: false, message: 'Cannot delete a super admin.' }, 403, request);
  }

  await auditDb.create(env.DB, {
    userId:    authUser.sub,
    tenantId:  authUser.tenant_id,
    action:    'admin.delete_user',
    model:     'User',
    modelId:   userId,
    oldData:   user,
    ipAddress: request.headers.get('CF-Connecting-IP') || '',
  });

  await userDb.delete(env.DB, userId);
  return jsonResponse({ success: true, message: 'User deleted successfully.' }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/admin/audit-logs
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminAuditLogs(request, env, authUser) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  const url    = new URL(request.url);
  const action = url.searchParams.get('action') || '';
  const model  = url.searchParams.get('model') || '';
  const userId = url.searchParams.get('user_id') || '';
  const limit  = parseInt(url.searchParams.get('limit') || '50');
  const offset = parseInt(url.searchParams.get('offset') || '0');

  const logs = await auditDb.list(env.DB, { action, model, userId, limit, offset });
  return jsonResponse({ success: true, data: logs }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/admin/stats
// ─────────────────────────────────────────────────────────────────────────────
export async function handleAdminStats(request, env, authUser) {
  const err = requireAdmin(authUser, request);
  if (err) return err;

  const stats = await statsDb.global(env.DB);
  return jsonResponse({ success: true, data: stats }, 200, request);
}
