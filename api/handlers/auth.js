/**
 * Auth Handler — Cloudflare Workers + D1
 * Routes: POST /api/auth/login, POST /api/auth/register,
 *         POST /api/auth/google, GET /api/auth/me, POST /api/auth/logout
 *
 * IP Capture Strategy:
 *   - Uses request.headers.get('CF-Connecting-IP') — the real client IP
 *     provided by Cloudflare's edge network.
 *   - ZERO browser permissions required. No navigator.geolocation, no
 *     device scanning, no user prompts of any kind.
 *   - IP is stored in audit_logs.ip_address on every login event.
 *   - Admin can retrieve per-user last IPs via the /api/admin/users endpoint.
 */

import { jsonResponse } from '../lib/cors.js';
import { signJwt, hashPassword, verifyPassword, generateToken } from '../lib/auth.js';
import { userDb, auditDb } from '../lib/db.js';

// ─────────────────────────────────────────────────────────────────────────────
// Helper: get client IP from Cloudflare headers (no browser permission needed)
// ─────────────────────────────────────────────────────────────────────────────
function getClientIp(request) {
  // CF-Connecting-IP is set by Cloudflare's edge — always the real user IP
  return (
    request.headers.get('CF-Connecting-IP') ||
    request.headers.get('X-Forwarded-For')?.split(',')[0]?.trim() ||
    request.headers.get('X-Real-IP') ||
    '0.0.0.0'
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Helper: build user response object (safe — no password hash)
// ─────────────────────────────────────────────────────────────────────────────
function userResponse(user) {
  return {
    id:             user.id,
    name:           user.name,
    email:          user.email,
    picture:        user.picture || null,
    is_super_admin: Boolean(user.is_super_admin),
    status:         user.status,
    tenant_id:      user.tenant_id,
    last_login_at:  user.last_login_at || null,
    created_at:     user.created_at,
  };
}

// ─────────────────────────────────────────────────────────────────────────────
// POST /api/auth/login
// ─────────────────────────────────────────────────────────────────────────────
export async function handleLogin(request, env) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { email, password } = body;
  if (!email || !password) {
    return jsonResponse({ success: false, message: 'Email and password are required.' }, 422, request);
  }

  const user = await userDb.findByEmail(env.DB, email.toLowerCase().trim());
  if (!user) {
    return jsonResponse({ success: false, message: 'Invalid email or password.' }, 401, request);
  }

  // Verify password (SHA-256 hash with email as salt for D1 users)
  // For Google-SSO-only accounts, password may be null
  if (!user.password) {
    return jsonResponse({ success: false, message: 'This account uses Google Sign-In. Please use the Google button.' }, 401, request);
  }

  const valid = await verifyPassword(password, user.password, user.email);
  if (!valid) {
    return jsonResponse({ success: false, message: 'Invalid email or password.' }, 401, request);
  }

  if (user.status !== 'active') {
    return jsonResponse({ success: false, message: `Your account is ${user.status}. Please contact support.` }, 403, request);
  }

  // Capture real IP — no browser permissions needed
  const ipAddress  = getClientIp(request);
  const userAgent  = request.headers.get('User-Agent') || '';

  // Update last login timestamp
  await userDb.updateLastLogin(env.DB, user.id);

  // Record audit log with IP
  await auditDb.create(env.DB, {
    userId:    user.id,
    tenantId:  user.tenant_id,
    action:    'login',
    ipAddress,
    userAgent,
  });

  const jwtSecret = env.JWT_SECRET || 'fawaz-platform-secret-change-in-production';
  const token = await signJwt({
    sub:            user.id,
    email:          user.email,
    is_super_admin: Boolean(user.is_super_admin),
    tenant_id:      user.tenant_id,
  }, jwtSecret);

  return jsonResponse({
    success: true,
    token,
    user: userResponse(user),
  }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// POST /api/auth/register
// ─────────────────────────────────────────────────────────────────────────────
export async function handleRegister(request, env) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { name, email, password, password_confirmation } = body;

  if (!name || !email || !password) {
    return jsonResponse({ success: false, message: 'Name, email and password are required.' }, 422, request);
  }
  if (password !== password_confirmation) {
    return jsonResponse({ success: false, message: 'Password confirmation does not match.' }, 422, request);
  }
  if (password.length < 8) {
    return jsonResponse({ success: false, message: 'Password must be at least 8 characters.' }, 422, request);
  }

  const existing = await userDb.findByEmail(env.DB, email.toLowerCase().trim());
  if (existing) {
    return jsonResponse({ success: false, message: 'An account with this email already exists.' }, 422, request);
  }

  // Hash password with email as salt
  const passwordHash = await hashPassword(password, email.toLowerCase().trim());

  const userId = await userDb.create(env.DB, {
    name:         name.trim(),
    email:        email.toLowerCase().trim(),
    passwordHash,
    tenantId:     1,
    isAdmin:      false,
  });

  const user = await userDb.findById(env.DB, userId);

  const jwtSecret = env.JWT_SECRET || 'fawaz-platform-secret-change-in-production';
  const token = await signJwt({
    sub:            user.id,
    email:          user.email,
    is_super_admin: false,
    tenant_id:      user.tenant_id,
  }, jwtSecret);

  return jsonResponse({
    success: true,
    token,
    user: userResponse(user),
  }, 201, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// POST /api/auth/google
// Verifies Google ID token and creates/updates the user record
// ─────────────────────────────────────────────────────────────────────────────
export async function handleGoogleLogin(request, env) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { id_token } = body;
  if (!id_token) {
    return jsonResponse({ success: false, message: 'Google id_token is required.' }, 422, request);
  }

  // Verify token with Google's tokeninfo endpoint
  let googleUser;
  try {
    const verifyResp = await fetch(
      `https://oauth2.googleapis.com/tokeninfo?id_token=${encodeURIComponent(id_token)}`
    );
    if (!verifyResp.ok) {
      return jsonResponse({ success: false, message: 'Invalid or expired Google token.' }, 401, request);
    }
    googleUser = await verifyResp.json();
  } catch {
    return jsonResponse({ success: false, message: 'Failed to verify Google token.' }, 401, request);
  }

  if (!googleUser.email || !googleUser.sub) {
    return jsonResponse({ success: false, message: 'Google token missing required fields.' }, 401, request);
  }

  // Find or create user
  let user = await userDb.findByEmail(env.DB, googleUser.email.toLowerCase());
  if (!user) {
    const userId = await userDb.create(env.DB, {
      name:     googleUser.name || 'Google User',
      email:    googleUser.email.toLowerCase(),
      googleId: googleUser.sub,
      picture:  googleUser.picture || null,
      tenantId: 1,
      isAdmin:  false,
    });
    user = await userDb.findById(env.DB, userId);
  } else if (!user.google_id) {
    await userDb.updateGoogleInfo(env.DB, user.id, googleUser.sub, googleUser.picture || null);
    user = await userDb.findById(env.DB, user.id);
  }

  if (user.status !== 'active') {
    return jsonResponse({ success: false, message: `Your account is ${user.status}.` }, 403, request);
  }

  const ipAddress = getClientIp(request);
  const userAgent = request.headers.get('User-Agent') || '';

  await userDb.updateLastLogin(env.DB, user.id);
  await auditDb.create(env.DB, {
    userId:    user.id,
    tenantId:  user.tenant_id,
    action:    'login',
    ipAddress,
    userAgent,
  });

  const jwtSecret = env.JWT_SECRET || 'fawaz-platform-secret-change-in-production';
  const token = await signJwt({
    sub:            user.id,
    email:          user.email,
    is_super_admin: Boolean(user.is_super_admin),
    tenant_id:      user.tenant_id,
  }, jwtSecret);

  return jsonResponse({
    success: true,
    token,
    user: userResponse(user),
  }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /api/auth/me  (requires auth — authUser injected by router)
// ─────────────────────────────────────────────────────────────────────────────
export async function handleMe(request, env, authUser) {
  if (!authUser) {
    return jsonResponse({ success: false, message: 'Unauthenticated.' }, 401, request);
  }
  const user = await userDb.findById(env.DB, authUser.sub);
  if (!user) {
    return jsonResponse({ success: false, message: 'User not found.' }, 404, request);
  }
  return jsonResponse({ success: true, user: userResponse(user) }, 200, request);
}

// ─────────────────────────────────────────────────────────────────────────────
// POST /api/auth/logout  (stateless JWT — just acknowledge, client drops token)
// ─────────────────────────────────────────────────────────────────────────────
export async function handleLogout(request, env, authUser) {
  if (authUser) {
    await auditDb.create(env.DB, {
      userId:    authUser.sub,
      tenantId:  authUser.tenant_id,
      action:    'logout',
      ipAddress: getClientIp(request),
      userAgent: request.headers.get('User-Agent') || '',
    });
  }
  return jsonResponse({ success: true, message: 'Logged out successfully.' }, 200, request);
}
