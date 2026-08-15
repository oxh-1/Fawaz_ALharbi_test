/**
 * Cloudflare Workers — Main Entry Point
 * Fawaz Platform API Router
 *
 * This file is the production backend that runs on Cloudflare's edge network.
 * It connects to Cloudflare D1 (fawaz_db) via the `env.DB` binding defined in wrangler.toml.
 *
 * All user IPs are captured via the `CF-Connecting-IP` header — which Cloudflare
 * sets automatically at the edge. No browser permissions are ever requested.
 *
 * Routes:
 *   Public:
 *     GET  /api/domain/check
 *     POST /api/auth/login
 *     POST /api/auth/register
 *     POST /api/auth/google
 *     POST /api/contact
 *
 *   Authenticated:
 *     GET  /api/auth/me
 *     POST /api/auth/logout
 *     GET  /api/contact
 *     GET  /api/contact/:id
 *     PATCH /api/contact/:id/read
 *
 *   Admin-only:
 *     GET    /api/admin/users
 *     GET    /api/admin/users/:id
 *     PUT    /api/admin/users/:id
 *     PATCH  /api/admin/users/:id/status
 *     DELETE /api/admin/users/:id
 *     GET    /api/admin/audit-logs
 *     GET    /api/admin/stats
 *     GET    /api/admin/domains
 *     POST   /api/admin/domains
 *     DELETE /api/admin/domains/:id
 *     DELETE /api/contact/:id
 *
 * All other requests are served by the static SPA assets (Vue build in /dist).
 */

import { handleOptions, jsonResponse, withCors } from './lib/cors.js';
import { getAuthUser } from './lib/auth.js';

// Auth handlers
import {
  handleLogin,
  handleRegister,
  handleGoogleLogin,
  handleMe,
  handleLogout,
} from './handlers/auth.js';

// Admin handlers
import {
  handleAdminUsers,
  handleAdminGetUser,
  handleAdminUpdateUser,
  handleAdminToggleStatus,
  handleAdminDeleteUser,
  handleAdminAuditLogs,
  handleAdminStats,
} from './handlers/admin.js';

// Contact handlers
import {
  handleContactSend,
  handleContactList,
  handleContactGet,
  handleContactMarkRead,
  handleContactDelete,
} from './handlers/contact.js';

// Domain handlers
import {
  handleDomainCheck,
  handleAdminDomainList,
  handleAdminDomainCreate,
  handleAdminDomainDelete,
} from './handlers/domain.js';

// ─────────────────────────────────────────────────────────────────────────────
// URL pattern matching utilities
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Match a URL path against a pattern with named segments.
 * Example: match('/api/admin/users/42', '/api/admin/users/:id') → { id: '42' }
 * @param {string} path - actual request path
 * @param {string} pattern - pattern with :param placeholders
 * @returns {Object|null} params object or null if no match
 */
function matchPath(path, pattern) {
  const patternParts = pattern.split('/');
  const pathParts    = path.split('/');

  if (patternParts.length !== pathParts.length) return null;

  const params = {};
  for (let i = 0; i < patternParts.length; i++) {
    if (patternParts[i].startsWith(':')) {
      params[patternParts[i].slice(1)] = pathParts[i];
    } else if (patternParts[i] !== pathParts[i]) {
      return null;
    }
  }
  return params;
}

// ─────────────────────────────────────────────────────────────────────────────
// Main fetch handler — entry point for all Cloudflare Worker requests
// ─────────────────────────────────────────────────────────────────────────────
export default {
  async fetch(request, env, ctx) {
    const url    = new URL(request.url);
    const path   = url.pathname;
    const method = request.method.toUpperCase();

    // Handle CORS preflight for all routes
    if (method === 'OPTIONS') {
      return handleOptions(request);
    }

    // Only handle /api/* routes — all others are served by the SPA assets
    if (!path.startsWith('/api/')) {
      return new Response(null, { status: 404 });
    }

    // Remove trailing slash for consistent matching
    const cleanPath = path.replace(/\/$/, '') || '/api';

    // Resolve authenticated user (JWT from Authorization header)
    const jwtSecret = env.JWT_SECRET || 'fawaz-platform-secret-change-in-production';
    const authUser  = await getAuthUser(request, jwtSecret);

    try {
      // ── Public Routes ─────────────────────────────────────────────────────

      // GET /api/domain/check
      if (method === 'GET' && cleanPath === '/api/domain/check') {
        return await handleDomainCheck(request, env);
      }

      // POST /api/auth/login
      if (method === 'POST' && cleanPath === '/api/auth/login') {
        return await handleLogin(request, env);
      }

      // POST /api/auth/register
      if (method === 'POST' && cleanPath === '/api/auth/register') {
        return await handleRegister(request, env);
      }

      // POST /api/auth/google
      if (method === 'POST' && cleanPath === '/api/auth/google') {
        return await handleGoogleLogin(request, env);
      }

      // POST /api/contact (public — submit form)
      if (method === 'POST' && cleanPath === '/api/contact') {
        return await handleContactSend(request, env);
      }

      // ── Authenticated Routes ──────────────────────────────────────────────

      // GET /api/auth/me
      if (method === 'GET' && cleanPath === '/api/auth/me') {
        return await handleMe(request, env, authUser);
      }

      // POST /api/auth/logout
      if (method === 'POST' && cleanPath === '/api/auth/logout') {
        return await handleLogout(request, env, authUser);
      }

      // GET /api/contact (list)
      if (method === 'GET' && cleanPath === '/api/contact') {
        return await handleContactList(request, env, authUser);
      }

      // ── Contact with ID ────────────────────────────────────────────────────

      let params;

      // GET /api/contact/:id
      params = matchPath(cleanPath, '/api/contact/:id');
      if (params && method === 'GET') {
        return await handleContactGet(request, env, authUser, params.id);
      }

      // PATCH /api/contact/:id/read
      params = matchPath(cleanPath, '/api/contact/:id/read');
      if (params && method === 'PATCH') {
        return await handleContactMarkRead(request, env, authUser, params.id);
      }

      // DELETE /api/contact/:id
      params = matchPath(cleanPath, '/api/contact/:id');
      if (params && method === 'DELETE') {
        return await handleContactDelete(request, env, authUser, params.id);
      }

      // ── Admin Routes ──────────────────────────────────────────────────────

      // GET /api/admin/users
      if (method === 'GET' && cleanPath === '/api/admin/users') {
        return await handleAdminUsers(request, env, authUser);
      }

      // GET /api/admin/audit-logs
      if (method === 'GET' && cleanPath === '/api/admin/audit-logs') {
        return await handleAdminAuditLogs(request, env, authUser);
      }

      // GET /api/admin/stats
      if (method === 'GET' && cleanPath === '/api/admin/stats') {
        return await handleAdminStats(request, env, authUser);
      }

      // GET /api/admin/domains
      if (method === 'GET' && cleanPath === '/api/admin/domains') {
        return await handleAdminDomainList(request, env, authUser);
      }

      // POST /api/admin/domains
      if (method === 'POST' && cleanPath === '/api/admin/domains') {
        return await handleAdminDomainCreate(request, env, authUser);
      }

      // GET /api/admin/users/:id
      params = matchPath(cleanPath, '/api/admin/users/:id');
      if (params && method === 'GET') {
        return await handleAdminGetUser(request, env, authUser, params.id);
      }

      // PUT /api/admin/users/:id
      params = matchPath(cleanPath, '/api/admin/users/:id');
      if (params && method === 'PUT') {
        return await handleAdminUpdateUser(request, env, authUser, params.id);
      }

      // PATCH /api/admin/users/:id/status
      params = matchPath(cleanPath, '/api/admin/users/:id/status');
      if (params && method === 'PATCH') {
        return await handleAdminToggleStatus(request, env, authUser, params.id);
      }

      // DELETE /api/admin/users/:id
      params = matchPath(cleanPath, '/api/admin/users/:id');
      if (params && method === 'DELETE') {
        return await handleAdminDeleteUser(request, env, authUser, params.id);
      }

      // DELETE /api/admin/domains/:id
      params = matchPath(cleanPath, '/api/admin/domains/:id');
      if (params && method === 'DELETE') {
        return await handleAdminDomainDelete(request, env, authUser, params.id);
      }

      // ── 404 Fallback ──────────────────────────────────────────────────────
      return jsonResponse({
        success: false,
        message: 'API endpoint not found.',
        path:    cleanPath,
        method,
      }, 404, request);

    } catch (error) {
      // Global error handler — log to console (appears in Cloudflare dashboard)
      console.error('[Workers API Error]', {
        path:    cleanPath,
        method,
        error:   error?.message || String(error),
        stack:   error?.stack,
      });

      return jsonResponse({
        success: false,
        message: 'Internal server error. Please try again later.',
        ...(env.APP_ENV !== 'production' ? { debug: error?.message } : {}),
      }, 500, request);
    }
  },
};
