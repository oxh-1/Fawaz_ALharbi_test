/**
 * JWT Auth Library — Cloudflare Workers compatible
 * Uses the Web Crypto API (crypto.subtle) — no Node.js or npm dependencies.
 * 
 * Tokens are HS256 JWTs signed with a secret stored as a Cloudflare Worker Secret.
 * Set the secret with: wrangler secret put JWT_SECRET
 */

const ALG = { name: 'HMAC', hash: 'SHA-256' };
const TOKEN_EXPIRY_SECONDS = 60 * 60 * 24 * 7; // 7 days

/**
 * Encodes an object as base64url (URL-safe base64, no padding).
 */
function base64urlEncode(data) {
  const str = typeof data === 'string' ? data : JSON.stringify(data);
  return btoa(unescape(encodeURIComponent(str)))
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=/g, '');
}

/**
 * Decodes a base64url string.
 */
function base64urlDecode(str) {
  const base64 = str.replace(/-/g, '+').replace(/_/g, '/');
  const padded = base64 + '='.repeat((4 - (base64.length % 4)) % 4);
  return decodeURIComponent(escape(atob(padded)));
}

/**
 * Derives a CryptoKey from the JWT secret string.
 * @param {string} secret
 * @returns {Promise<CryptoKey>}
 */
async function getKey(secret) {
  const encoder = new TextEncoder();
  return crypto.subtle.importKey(
    'raw',
    encoder.encode(secret),
    ALG,
    false,
    ['sign', 'verify']
  );
}

/**
 * Signs a JWT payload and returns the full JWT token string.
 * @param {Object} payload  — data to encode (e.g. { sub: userId, email, is_super_admin })
 * @param {string} secret   — JWT signing secret
 * @returns {Promise<string>} JWT token
 */
export async function signJwt(payload, secret) {
  const header = base64urlEncode({ alg: 'HS256', typ: 'JWT' });
  const now = Math.floor(Date.now() / 1000);
  const fullPayload = base64urlEncode({
    ...payload,
    iat: now,
    exp: now + TOKEN_EXPIRY_SECONDS,
  });

  const signingInput = `${header}.${fullPayload}`;
  const key = await getKey(secret);
  const encoder = new TextEncoder();
  const signature = await crypto.subtle.sign('HMAC', key, encoder.encode(signingInput));

  const sigBase64 = btoa(String.fromCharCode(...new Uint8Array(signature)))
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=/g, '');

  return `${signingInput}.${sigBase64}`;
}

/**
 * Verifies a JWT token and returns its decoded payload.
 * Throws an error if invalid or expired.
 * @param {string} token
 * @param {string} secret
 * @returns {Promise<Object>} decoded payload
 */
export async function verifyJwt(token, secret) {
  const parts = token.split('.');
  if (parts.length !== 3) throw new Error('Invalid token format');

  const [headerB64, payloadB64, sigB64] = parts;
  const signingInput = `${headerB64}.${payloadB64}`;

  // Decode and verify signature
  const key = await getKey(secret);
  const encoder = new TextEncoder();
  const sigBytes = Uint8Array.from(
    atob(sigB64.replace(/-/g, '+').replace(/_/g, '/')),
    c => c.charCodeAt(0)
  );

  const valid = await crypto.subtle.verify('HMAC', key, sigBytes, encoder.encode(signingInput));
  if (!valid) throw new Error('Invalid token signature');

  // Decode payload
  const payload = JSON.parse(base64urlDecode(payloadB64));

  // Check expiry
  const now = Math.floor(Date.now() / 1000);
  if (payload.exp && payload.exp < now) throw new Error('Token has expired');

  return payload;
}

/**
 * Extracts and verifies the Bearer token from an Authorization header.
 * Returns the decoded payload or null if invalid.
 * @param {Request} request
 * @param {string} secret
 * @returns {Promise<Object|null>}
 */
export async function getAuthUser(request, secret) {
  const authHeader = request.headers.get('Authorization') || '';
  if (!authHeader.startsWith('Bearer ')) return null;
  const token = authHeader.slice(7);
  try {
    return await verifyJwt(token, secret);
  } catch {
    return null;
  }
}

/**
 * Hashes a password with SHA-256 + salt using Web Crypto.
 * @param {string} password
 * @param {string} salt — unique per-user random string
 * @returns {Promise<string>} hex hash
 */
export async function hashPassword(password, salt = '') {
  const encoder = new TextEncoder();
  const data = encoder.encode(password + salt);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  return Array.from(new Uint8Array(hashBuffer))
    .map(b => b.toString(16).padStart(2, '0'))
    .join('');
}

/**
 * Verifies a password against a stored hash.
 * @param {string} password
 * @param {string} storedHash
 * @param {string} salt
 * @returns {Promise<boolean>}
 */
export async function verifyPassword(password, storedHash, salt = '') {
  const hash = await hashPassword(password, salt);
  return hash === storedHash;
}

/**
 * Generates a cryptographically secure random token string.
 * @param {number} byteLength
 * @returns {string} hex string
 */
export function generateToken(byteLength = 32) {
  const bytes = new Uint8Array(byteLength);
  crypto.getRandomValues(bytes);
  return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
}
