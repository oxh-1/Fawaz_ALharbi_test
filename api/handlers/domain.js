/**
 * Domain Check Handler — Cloudflare Workers + D1
 * Route: GET /api/domain/check?host=example.com
 *
 * Validates that the requesting domain is a registered, active tenant domain.
 * Called by the Vue frontend on every app load to resolve the correct tenant context.
 */

import { jsonResponse } from '../lib/cors.js';
import { domainDb } from '../lib/db.js';

const DEV_HOSTS = ['localhost', '127.0.0.1', '0.0.0.0'];

/**
 * GET /api/domain/check?host=<hostname>
 */
export async function handleDomainCheck(request, env) {
  const url  = new URL(request.url);
  let host   = url.searchParams.get('host') || url.hostname;

  // Strip port if present
  host = host.replace(/:\d+$/, '').toLowerCase().trim();

  // Always allow development hosts without a DB lookup
  if (DEV_HOSTS.includes(host) || host.endsWith('.pages.dev') || host.endsWith('.workers.dev')) {
    return jsonResponse({
      valid:     true,
      tenant_id: 1,
      domain:    host,
      type:      host.includes('.pages.dev') || host.includes('.workers.dev') ? 'staging' : 'development',
    }, 200, request);
  }

  // Look up domain in D1
  const row = await domainDb.findByHost(env.DB, host);

  if (!row) {
    return jsonResponse({
      valid:   false,
      domain:  host,
      message: `Domain '${host}' is not registered in this platform.`,
    }, 403, request);
  }

  // Check tenant status
  if (row.t_status && row.t_status !== 'active') {
    return jsonResponse({
      valid:   false,
      domain:  host,
      message: `The tenant for this domain is ${row.t_status}.`,
    }, 403, request);
  }

  return jsonResponse({
    valid:     true,
    tenant_id: row.tenant_id,
    domain:    host,
    type:      row.type,
    tenant: {
      id:   row.t_id,
      name: row.t_name,
      slug: row.t_slug,
    },
  }, 200, request);
}

/**
 * GET /api/admin/domains — Auth + Admin
 */
export async function handleAdminDomainList(request, env, authUser) {
  if (!authUser?.is_super_admin) {
    return jsonResponse({ success: false, message: 'Admin access required.' }, 403, request);
  }
  const domains = await domainDb.list(env.DB);
  return jsonResponse({ success: true, data: domains }, 200, request);
}

/**
 * POST /api/admin/domains — Auth + Admin
 */
export async function handleAdminDomainCreate(request, env, authUser) {
  if (!authUser?.is_super_admin) {
    return jsonResponse({ success: false, message: 'Admin access required.' }, 403, request);
  }

  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { domain, tenant_id, type } = body;
  if (!domain || !tenant_id || !type) {
    return jsonResponse({ success: false, message: 'domain, tenant_id, and type are required.' }, 422, request);
  }

  const id = await domainDb.create(env.DB, domain.toLowerCase().trim(), tenant_id, type);
  return jsonResponse({ success: true, data: { id, domain, tenant_id, type } }, 201, request);
}

/**
 * DELETE /api/admin/domains/:id — Auth + Admin
 */
export async function handleAdminDomainDelete(request, env, authUser, id) {
  if (!authUser?.is_super_admin) {
    return jsonResponse({ success: false, message: 'Admin access required.' }, 403, request);
  }

  await domainDb.delete(env.DB, id);
  return jsonResponse({ success: true, message: 'Domain removed.' }, 200, request);
}
