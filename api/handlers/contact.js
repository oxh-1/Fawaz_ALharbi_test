/**
 * Contact Messages Handler — Cloudflare Workers + D1
 * Routes:
 *   POST /api/contact         — Public: submit a contact message
 *   GET  /api/contact         — Auth: list messages (admin sees all, users see own tenant)
 *   GET  /api/contact/:id     — Auth: get a single message
 *   PATCH /api/contact/:id/read — Auth: mark as read
 *   DELETE /api/contact/:id   — Auth/Admin: delete a message
 */

import { jsonResponse } from '../lib/cors.js';
import { contactDb, auditDb } from '../lib/db.js';

// ─────────────────────────────────────────────────────────────────────────────
// Helper: get client IP from Cloudflare — no browser permissions
// ─────────────────────────────────────────────────────────────────────────────
function getClientIp(request) {
  return (
    request.headers.get('CF-Connecting-IP') ||
    request.headers.get('X-Forwarded-For')?.split(',')[0]?.trim() ||
    '0.0.0.0'
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// POST /api/contact — Public, no auth required
// ─────────────────────────────────────────────────────────────────────────────
export async function handleContactSend(request, env) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { name, email, subject, message, tenant_id } = body;

  if (!name || !email || !subject || !message) {
    return jsonResponse({
      success: false,
      message: 'Name, email, subject, and message are required.',
    }, 422, request);
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    return jsonResponse({ success: false, message: 'Invalid email address.' }, 422, request);
  }

  // Message length guard
  if (message.length > 5000) {
    return jsonResponse({ success: false, message: 'Message must be under 5000 characters.' }, 422, request);
  }

  // Capture IP server-side — zero browser permissions
  const ipAddress = getClientIp(request);

  const id = await contactDb.create(env.DB, {
    tenantId: tenant_id || 1,
    name:     name.trim(),
    email:    email.toLowerCase().trim(),
    subject:  subject.trim(),
    message:  message.trim(),
    ipAddress,
  });

  return jsonResponse({
    success: true,
    message: 'Your message has been sent successfully. We will get back to you at ' + email,
    data: { id },
  }, 201, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/contact — Auth required
// ─────────────────────────────────────────────────────────────────────────────
export async function handleContactList(request, env, authUser) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }

  const tenantId = authUser.is_super_admin ? null : (authUser.tenant_id || 1);
  const messages = await contactDb.list(env.DB, tenantId || 1);

  return jsonResponse({ success: true, data: messages }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/contact/:id — Auth required
// ─────────────────────────────────────────────────────────────────────────────
export async function handleContactGet(request, env, authUser, id) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }

  const msg = await contactDb.findById(env.DB, id);
  if (!msg) {
    return jsonResponse({ success: false, message: 'Message not found.' }, 404, request);
  }

  return jsonResponse({ success: true, data: msg }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// PATCH /api/contact/:id/read — Auth required
// ─────────────────────────────────────────────────────────────────────────────
export async function handleContactMarkRead(request, env, authUser, id) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }

  const msg = await contactDb.findById(env.DB, id);
  if (!msg) {
    return jsonResponse({ success: false, message: 'Message not found.' }, 404, request);
  }

  await contactDb.markRead(env.DB, id, authUser.sub);
  return jsonResponse({ success: true, message: 'Message marked as read.' }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// DELETE /api/contact/:id — Auth + Admin required
// ─────────────────────────────────────────────────────────────────────────────
export async function handleContactDelete(request, env, authUser, id) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }
  if (!authUser.is_super_admin) {
    return jsonResponse({ success: false, message: 'Only admins can delete messages.' }, 403, request);
  }

  await contactDb.delete(env.DB, id);
  return jsonResponse({ success: true, message: 'Message deleted.' }, 200, request);
}
