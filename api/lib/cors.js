/**
 * CORS Helper — Production-ready headers for Cloudflare Workers
 * Handles preflight OPTIONS requests and attaches CORS headers to all responses.
 */

const ALLOWED_ORIGINS = [
  'https://fawazalharbi.pages.dev',
  'https://fawazalharbi.dev',
  'http://localhost:8080',
  'http://localhost:5173',
  'http://127.0.0.1:8080',
];

/**
 * Returns CORS headers for a given request origin.
 * @param {Request} request
 * @returns {Object} headers object
 */
export function getCorsHeaders(request) {
  const origin = request.headers.get('Origin') || '';
  const allowedOrigin = ALLOWED_ORIGINS.includes(origin) ? origin : ALLOWED_ORIGINS[0];

  return {
    'Access-Control-Allow-Origin': allowedOrigin,
    'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization, Accept, X-Requested-With',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Max-Age': '86400',
    'Vary': 'Origin',
  };
}

/**
 * Wraps a Response with CORS headers.
 * @param {Response} response
 * @param {Request} request
 * @returns {Response}
 */
export function withCors(response, request) {
  const corsHeaders = getCorsHeaders(request);
  const newHeaders = new Headers(response.headers);
  for (const [key, value] of Object.entries(corsHeaders)) {
    newHeaders.set(key, value);
  }
  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers: newHeaders,
  });
}

/**
 * Handles OPTIONS preflight requests.
 * @param {Request} request
 * @returns {Response}
 */
export function handleOptions(request) {
  return new Response(null, {
    status: 204,
    headers: getCorsHeaders(request),
  });
}

/**
 * Creates a JSON response with CORS headers.
 * @param {*} data
 * @param {number} status
 * @param {Request} request
 * @returns {Response}
 */
export function jsonResponse(data, status = 200, request = null) {
  const headers = {
    'Content-Type': 'application/json',
    ...(request ? getCorsHeaders(request) : {}),
  };
  return new Response(JSON.stringify(data), { status, headers });
}
