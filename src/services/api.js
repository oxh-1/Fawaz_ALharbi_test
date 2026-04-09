/**
 * Company 2 — Frontend API Service Layer
 * All backend communication goes through this module.
 * Base URL reads from Vue environment variable: VUE_APP_API_URL
 */

const BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost/backend/public/api';

// ─────────────────────────────────────────────────────────────────────────────
// Core HTTP helper
// ─────────────────────────────────────────────────────────────────────────────
async function http(method, endpoint, body = null, options = {}) {
  const token = localStorage.getItem('api_token');

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  const config = {
    method: method.toUpperCase(),
    headers,
  };

  if (body && !['GET', 'HEAD'].includes(method.toUpperCase())) {
    config.body = JSON.stringify(body);
  }

  const url = new URL(endpoint.startsWith('http') ? endpoint : `${BASE_URL}/${endpoint.replace(/^\//, '')}`);

  // Append query params for GET requests
  if (options.params) {
    Object.entries(options.params).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') url.searchParams.set(k, v);
    });
  }

  const response = await fetch(url.toString(), config);

  let data;
  try { data = await response.json(); } catch { data = {}; }

  if (!response.ok) {
    const error = new Error(data.message || `HTTP ${response.status}`);
    error.status = response.status;
    error.errors = data.errors || {};
    error.data = data;
    throw error;
  }

  return data;
}

// ─────────────────────────────────────────────────────────────────────────────
// Auth API
// ─────────────────────────────────────────────────────────────────────────────
export const authApi = {
  login:       (email, password) => http('POST', 'auth/login', { email, password }),
  register:    (name, email, password, password_confirmation) =>
    http('POST', 'auth/register', { name, email, password, password_confirmation }),
  googleLogin: (id_token) => http('POST', 'auth/google', { id_token }),
  me:          () => http('GET', 'auth/me'),
  logout:      () => http('POST', 'auth/logout'),
};

// ─────────────────────────────────────────────────────────────────────────────
// Domain Validation
// ─────────────────────────────────────────────────────────────────────────────
export const domainApi = {
  check: (host = window.location.hostname) =>
    http('GET', `domain/check`, null, { params: { host } }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Merchants
// ─────────────────────────────────────────────────────────────────────────────
export const merchantApi = {
  list:         (params = {}) => http('GET', 'merchants', null, { params }),
  get:          (id)          => http('GET', `merchants/${id}`),
  create:       (data)        => http('POST', 'merchants', data),
  update:       (id, data)    => http('PUT', `merchants/${id}`, data),
  delete:       (id)          => http('DELETE', `merchants/${id}`),
  updateStatus: (id, status)  => http('PATCH', `merchants/${id}/status`, { status }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Categories
// ─────────────────────────────────────────────────────────────────────────────
export const categoryApi = {
  list:    (params = {}) => http('GET', 'categories', null, { params }),
  get:     (id)          => http('GET', `categories/${id}`),
  create:  (data)        => http('POST', 'categories', data),
  update:  (id, data)    => http('PUT', `categories/${id}`, data),
  delete:  (id)          => http('DELETE', `categories/${id}`),
  reorder: (ids)         => http('POST', 'categories/reorder', { ids }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Services
// ─────────────────────────────────────────────────────────────────────────────
export const serviceApi = {
  list:   (params = {}) => http('GET', 'services', null, { params }),
  get:    (id)          => http('GET', `services/${id}`),
  create: (data)        => http('POST', 'services', data),
  update: (id, data)    => http('PUT', `services/${id}`, data),
  delete: (id)          => http('DELETE', `services/${id}`),
  toggle: (id)          => http('PATCH', `services/${id}/toggle`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Bookings
// ─────────────────────────────────────────────────────────────────────────────
export const bookingApi = {
  list:         (params = {}) => http('GET', 'bookings', null, { params }),
  get:          (id)          => http('GET', `bookings/${id}`),
  create:       (data)        => http('POST', 'bookings', data),
  update:       (id, data)    => http('PUT', `bookings/${id}`, data),
  delete:       (id)          => http('DELETE', `bookings/${id}`),
  updateStatus: (id, status)  => http('PATCH', `bookings/${id}/status`, { status }),
  calendar:     (year, month) => http('GET', `bookings/calendar/${year}/${month}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Reviews
// ─────────────────────────────────────────────────────────────────────────────
export const reviewApi = {
  list:     (params = {})    => http('GET', 'reviews', null, { params }),
  get:      (id)             => http('GET', `reviews/${id}`),
  create:   (data)           => http('POST', 'reviews', data),
  delete:   (id)             => http('DELETE', `reviews/${id}`),
  moderate: (id, status, reason = '') =>
    http('PATCH', `reviews/${id}/moderate`, { status, reject_reason: reason }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Contact Messages
// ─────────────────────────────────────────────────────────────────────────────
export const contactApi = {
  list:    (params = {}) => http('GET', 'contact', null, { params }),
  get:     (id)          => http('GET', `contact/${id}`),
  send:    (data)        => http('POST', 'contact', data),
  delete:  (id)          => http('DELETE', `contact/${id}`),
  markRead:(id)          => http('PATCH', `contact/${id}/read`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Pricing Plans
// ─────────────────────────────────────────────────────────────────────────────
export const pricingApi = {
  list:   (params = {}) => http('GET', 'pricing', null, { params }),
  get:    (id)          => http('GET', `pricing/${id}`),
  create: (data)        => http('POST', 'pricing', data),
  update: (id, data)    => http('PUT', `pricing/${id}`, data),
  delete: (id)          => http('DELETE', `pricing/${id}`),
  toggle: (id)          => http('PATCH', `pricing/${id}/toggle`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Ads
// ─────────────────────────────────────────────────────────────────────────────
export const adApi = {
  list:   (params = {}) => http('GET', 'ads', null, { params }),
  get:    (id)          => http('GET', `ads/${id}`),
  create: (data)        => http('POST', 'ads', data),
  update: (id, data)    => http('PUT', `ads/${id}`, data),
  delete: (id)          => http('DELETE', `ads/${id}`),
  toggle: (id)          => http('PATCH', `ads/${id}/toggle`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Content Pages
// ─────────────────────────────────────────────────────────────────────────────
export const contentApi = {
  list:          (params = {}) => http('GET', 'content', null, { params }),
  get:           (id)          => http('GET', `content/${id}`),
  create:        (data)        => http('POST', 'content', data),
  update:        (id, data)    => http('PUT', `content/${id}`, data),
  delete:        (id)          => http('DELETE', `content/${id}`),
  togglePublish: (id)          => http('PATCH', `content/${id}/publish`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Settlements
// ─────────────────────────────────────────────────────────────────────────────
export const settlementApi = {
  list:   (params = {}) => http('GET', 'settlements', null, { params }),
  get:    (id)          => http('GET', `settlements/${id}`),
  export: (params = {}) => http('POST', 'settlements/export', params),
};

// ─────────────────────────────────────────────────────────────────────────────
// Reports
// ─────────────────────────────────────────────────────────────────────────────
export const reportApi = {
  summary:       (params = {}) => http('GET', 'reports/summary', null, { params }),
  revenue:       (params = {}) => http('GET', 'reports/revenue', null, { params }),
  bookingStats:  (params = {}) => http('GET', 'reports/bookings', null, { params }),
  merchantStats: (params = {}) => http('GET', 'reports/merchants', null, { params }),
  export:        (params = {}) => http('POST', 'reports/export', params),
};

// ─────────────────────────────────────────────────────────────────────────────
// Permissions (RBAC)
// ─────────────────────────────────────────────────────────────────────────────
export const permissionApi = {
  list:              ()                    => http('GET', 'permissions'),
  roles:             ()                    => http('GET', 'permissions/roles'),
  rolePermissions:   (roleId)              => http('GET', `permissions/roles/${roleId}`),
  syncRolePerms:     (roleId, permIds)     => http('POST', `permissions/roles/${roleId}/sync`, { permission_ids: permIds }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Notifications
// ─────────────────────────────────────────────────────────────────────────────
export const notificationApi = {
  getSettings: () => http('GET', 'notifications/settings'),
  saveSettings:(settings) => http('PUT', 'notifications/settings', { settings }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Settings
// ─────────────────────────────────────────────────────────────────────────────
export const settingApi = {
  list:            ()       => http('GET', 'settings'),
  update:          (data)   => http('PUT', 'settings', data),
  updateCompany:   (data)   => http('PUT', 'settings/company', data),
  updatePassword:  (data)   => http('PUT', 'settings/password', data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Admin
// ─────────────────────────────────────────────────────────────────────────────
export const adminApi = {
  users:            (params = {}) => http('GET', 'admin/users', null, { params }),
  getUser:          (id)          => http('GET', `admin/users/${id}`),
  updateUser:       (id, data)    => http('PUT', `admin/users/${id}`, data),
  toggleUserStatus: (id, status)  => http('PATCH', `admin/users/${id}/status`, { status }),
  deleteUser:       (id)          => http('DELETE', `admin/users/${id}`),
  auditLogs:        (params = {}) => http('GET', 'admin/audit-logs', null, { params }),
  globalStats:      ()            => http('GET', 'admin/stats'),
  tenants:          ()            => http('GET', 'admin/tenants'),
  domains:          ()            => http('GET', 'admin/domains'),
  addDomain:        (data)        => http('POST', 'admin/domains', data),
  deleteDomain:     (id)          => http('DELETE', `admin/domains/${id}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Token management helpers
// ─────────────────────────────────────────────────────────────────────────────
export function setToken(token) {
  localStorage.setItem('api_token', token);
}

export function clearToken() {
  localStorage.removeItem('api_token');
}

export function getToken() {
  return localStorage.getItem('api_token');
}
