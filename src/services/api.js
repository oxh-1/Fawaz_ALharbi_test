/**
 * Fawaz Platform — Frontend API Service Layer
 * Centralized HTTP client with global error handling, retries, and logging.
 * Base URL reads from Vue environment variable: VUE_APP_API_URL
 * Production: connects to Cloudflare Workers at /api (same domain, no CORS)
 * Local dev:  wrangler dev serves Workers + static at http://localhost:8787
 */

import axios from 'axios';

// ─────────────────────────────────────────────────────────────────────────────
// AXIOS INSTANCE CONFIGURATION
// ─────────────────────────────────────────────────────────────────────────────
const apiClient = axios.create({
  // In production on Cloudflare, the Workers API is at /api (same origin — no CORS)
  // In local dev with wrangler dev, it's also at /api via the same localhost port
  baseURL: process.env.VUE_APP_API_URL || '/api',
  timeout: parseInt(process.env.VUE_APP_API_TIMEOUT) || 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// ─────────────────────────────────────────────────────────────────────────────
// REQUEST INTERCEPTOR
// Runs BEFORE every request — injects auth token and logs in dev mode
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    if (process.env.VUE_APP_DEBUG === 'true') {
      console.log(`📤 API Request: ${config.method.toUpperCase()} ${config.url}`, {
        data: config.data,
        params: config.params,
      });
    }

    return config;
  },
  (error) => {
    console.error('❌ Request setup error:', error);
    return Promise.reject(error);
  }
);

// ─────────────────────────────────────────────────────────────────────────────
// RESPONSE INTERCEPTOR
// Runs AFTER every response — handles errors globally and unwraps data
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.response.use(
  // SUCCESS (2xx)
  (response) => {
    if (process.env.VUE_APP_DEBUG === 'true') {
      console.log(`📥 API Response: ${response.status}`, response.data);
    }
    return response.data;
  },

  // ERROR (4xx / 5xx / network)
  (error) => {
    const status = error.response?.status;
    const data   = error.response?.data;

    // 401 — token expired / invalid → force logout
    if (status === 401) {
      // Do not trigger global logout loop if this was an auth request
      if (error.config && (error.config.url.includes('auth/login') || error.config.url.includes('auth/logout'))) {
         return Promise.reject({ status: 401, message: data?.message || 'Unauthorized', errors: {} });
      }

      console.warn('⚠️  Token expired, logging out...');
      localStorage.removeItem('auth_token');
      localStorage.removeItem('loggedInUser');

      // Lazy-import store to avoid circular dependency
      import('@/store').then(({ default: store }) => {
        store.dispatch('logout');
      });

      // Only redirect if not already on login
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }

      return Promise.reject({ status: 401, message: 'Your session has expired. Please log in again.', errors: {} });
    }

    // 403 — forbidden
    if (status === 403) {
      console.warn('⚠️  Permission denied');
      return Promise.reject({ status: 403, message: 'You do not have permission to perform this action.', errors: {} });
    }

    // 422 — validation errors from backend
    if (status === 422) {
      console.warn('⚠️  Validation error:', data?.errors);
      return Promise.reject({
        status: 422,
        message: data?.message || 'Please check the form and correct any errors.',
        errors: data?.errors || {},
      });
    }

    // 500 — server crash
    if (status === 500) {
      console.error('🔴 Server error:', data);
      return Promise.reject({ status: 500, message: 'Server error. Please try again later.', errors: {} });
    }

    // Timeout
    if (error.code === 'ECONNABORTED') {
      console.error('🔴 Request timeout');
      return Promise.reject({ status: 0, message: 'Request timed out. Please check your internet and try again.', errors: {} });
    }

    // No response (offline / CORS / server down)
    if (!error.response) {
      console.error('🔴 Network error:', error.message);
      return Promise.reject({ status: 0, message: 'Unable to connect to server. Please check your internet connection.', errors: {} });
    }

    // Generic fallback
    console.error('❌ API Error:', data);
    return Promise.reject({
      status: status || 500,
      message: data?.message || error.message || 'An unexpected error occurred.',
      errors: data?.errors || {},
    });
  }
);

// ─────────────────────────────────────────────────────────────────────────────
// Auth API
// ─────────────────────────────────────────────────────────────────────────────
export const authApi = {
  login:           (email, password)                                  => apiClient.post('auth/login', { email, password }),
  register:        (name, email, password, password_confirmation)     => apiClient.post('auth/register', { name, email, password, password_confirmation }),
  googleLogin:     (id_token)                                         => apiClient.post('auth/google', { id_token }),
  me:              ()                                                  => apiClient.get('auth/me'),
  logout:          ()                                                  => apiClient.post('auth/logout'),
  refreshToken:    ()                                                  => apiClient.post('auth/refresh'),
  updateProfile:   (data)                                             => apiClient.put('auth/profile', data),
  changePassword:  (old_password, new_password)                       => apiClient.post('auth/change-password', { old_password, new_password }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Domain Validation
// ─────────────────────────────────────────────────────────────────────────────
export const domainApi = {
  check: (host = window.location.hostname) => apiClient.get('domain/check', { params: { host } }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Merchants
// ─────────────────────────────────────────────────────────────────────────────
export const merchantApi = {
  list:         (params = {}) => apiClient.get('merchants', { params }),
  get:          (id)          => apiClient.get(`merchants/${id}`),
  create:       (data)        => apiClient.post('merchants', data),
  update:       (id, data)    => apiClient.put(`merchants/${id}`, data),
  delete:       (id)          => apiClient.delete(`merchants/${id}`),
  updateStatus: (id, status)  => apiClient.patch(`merchants/${id}/status`, { status }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Categories
// ─────────────────────────────────────────────────────────────────────────────
export const categoryApi = {
  list:    (params = {}) => apiClient.get('categories', { params }),
  get:     (id)          => apiClient.get(`categories/${id}`),
  create:  (data)        => apiClient.post('categories', data),
  update:  (id, data)    => apiClient.put(`categories/${id}`, data),
  delete:  (id)          => apiClient.delete(`categories/${id}`),
  reorder: (ids)         => apiClient.post('categories/reorder', { ids }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Services
// ─────────────────────────────────────────────────────────────────────────────
export const serviceApi = {
  list:   (params = {}) => apiClient.get('services', { params }),
  get:    (id)          => apiClient.get(`services/${id}`),
  create: (data)        => apiClient.post('services', data),
  update: (id, data)    => apiClient.put(`services/${id}`, data),
  delete: (id)          => apiClient.delete(`services/${id}`),
  toggle: (id)          => apiClient.patch(`services/${id}/toggle`),
};


// ─────────────────────────────────────────────────────────────────────────────
// Bookings
// ─────────────────────────────────────────────────────────────────────────────
export const bookingApi = {
  list:         (params = {}) => apiClient.get('bookings', { params }),
  get:          (id)          => apiClient.get(`bookings/${id}`),
  create:       (data)        => apiClient.post('bookings', data),
  update:       (id, data)    => apiClient.put(`bookings/${id}`, data),
  delete:       (id)          => apiClient.delete(`bookings/${id}`),
  updateStatus: (id, status)  => apiClient.patch(`bookings/${id}/status`, { status }),
  calendar:     (year, month) => apiClient.get(`bookings/calendar/${year}/${month}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Customers
// ─────────────────────────────────────────────────────────────────────────────
export const customerApi = {
  list:   (params = {}) => apiClient.get('customers', { params }),
  get:    (id)          => apiClient.get(`customers/${id}`),
  create: (data)        => apiClient.post('customers', data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Reviews
// ─────────────────────────────────────────────────────────────────────────────
export const reviewApi = {
  list:     (params = {})               => apiClient.get('reviews', { params }),
  get:      (id)                        => apiClient.get(`reviews/${id}`),
  create:   (data)                      => apiClient.post('reviews', data),
  delete:   (id)                        => apiClient.delete(`reviews/${id}`),
  moderate: (id, status, reason = '')   => apiClient.patch(`reviews/${id}/moderate`, { status, reject_reason: reason }),
};

export const testimonialApi = reviewApi;

// ─────────────────────────────────────────────────────────────────────────────
// Contact Messages
// ─────────────────────────────────────────────────────────────────────────────
export const contactApi = {
  list:     (params = {}) => apiClient.get('contact', { params }),
  get:      (id)          => apiClient.get(`contact/${id}`),
  send:     (data)        => apiClient.post('contact', data),
  delete:   (id)          => apiClient.delete(`contact/${id}`),
  markRead: (id)          => apiClient.patch(`contact/${id}/read`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Pricing Plans
// ─────────────────────────────────────────────────────────────────────────────
export const pricingApi = {
  list:   (params = {}) => apiClient.get('pricing', { params }),
  get:    (id)          => apiClient.get(`pricing/${id}`),
  create: (data)        => apiClient.post('pricing', data),
  update: (id, data)    => apiClient.put(`pricing/${id}`, data),
  delete: (id)          => apiClient.delete(`pricing/${id}`),
  toggle: (id)          => apiClient.patch(`pricing/${id}/toggle`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Ads
// ─────────────────────────────────────────────────────────────────────────────
export const adApi = {
  list:   (params = {}) => apiClient.get('ads', { params }),
  get:    (id)          => apiClient.get(`ads/${id}`),
  create: (data)        => apiClient.post('ads', data),
  update: (id, data)    => apiClient.put(`ads/${id}`, data),
  delete: (id)          => apiClient.delete(`ads/${id}`),
  toggle: (id)          => apiClient.patch(`ads/${id}/toggle`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Content Pages
// ─────────────────────────────────────────────────────────────────────────────
export const contentApi = {
  list:          (params = {}) => apiClient.get('content', { params }),
  get:           (id)          => apiClient.get(`content/${id}`),
  create:        (data)        => apiClient.post('content', data),
  update:        (id, data)    => apiClient.put(`content/${id}`, data),
  delete:        (id)          => apiClient.delete(`content/${id}`),
  togglePublish: (id)          => apiClient.patch(`content/${id}/publish`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Settlements
// ─────────────────────────────────────────────────────────────────────────────
export const settlementApi = {
  list:   (params = {}) => apiClient.get('settlements', { params }),
  get:    (id)          => apiClient.get(`settlements/${id}`),
  export: (params = {}) => apiClient.post('settlements/export', params),
};

// ─────────────────────────────────────────────────────────────────────────────
// Reports
// ─────────────────────────────────────────────────────────────────────────────
export const reportApi = {
  summary:       (params = {}) => apiClient.get('reports/summary', { params }),
  revenue:       (params = {}) => apiClient.get('reports/revenue', { params }),
  bookingStats:  (params = {}) => apiClient.get('reports/bookings', { params }),
  merchantStats: (params = {}) => apiClient.get('reports/merchants', { params }),
  export:        (params = {}) => apiClient.post('reports/export', params),
};

// ─────────────────────────────────────────────────────────────────────────────
// Permissions (RBAC)
// ─────────────────────────────────────────────────────────────────────────────
export const permissionApi = {
  list:            ()                => apiClient.get('permissions'),
  roles:           ()                => apiClient.get('permissions/roles'),
  rolePermissions: (roleId)          => apiClient.get(`permissions/roles/${roleId}`),
  syncRolePerms:   (roleId, permIds) => apiClient.post(`permissions/roles/${roleId}/sync`, { permission_ids: permIds }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Notifications
// ─────────────────────────────────────────────────────────────────────────────
export const notificationApi = {
  getSettings: () => apiClient.get('notifications/settings'),
  saveSettings: (settings) => apiClient.put('notifications/settings', { settings }),
  updateSettings: (data) => apiClient.post('notifications/settings', data),
  testEmail: (data) => apiClient.post('notifications/test-email', data),
  getLogs: (params) => apiClient.get('notifications/logs', { params }),
};

export const chatApi = {
  list: () => apiClient.get('chat'),
  send: (message) => apiClient.post('chat', { message }),
};

export const c2Api = {
  list: (type) => apiClient.get(`c2/${type}`),
  create: (type, data) => apiClient.post(`c2/${type}`, data),
  update: (type, id, data) => apiClient.put(`c2/${type}/${id}`, data),
  delete: (type, id) => apiClient.delete(`c2/${type}/${id}`),
  getSettings: () => apiClient.get('c2/settings'),
  updateSettings: (data) => apiClient.put('c2/settings', data)
};

// ─────────────────────────────────────────────────────────────────────────────
// Settings
// ─────────────────────────────────────────────────────────────────────────────
export const settingApi = {
  list:           ()     => apiClient.get('settings'),
  update:         (data) => apiClient.put('settings', data),
  updateCompany:  (data) => apiClient.put('settings/company', data),
  updatePassword: (data) => apiClient.put('settings/password', data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Admin
// ─────────────────────────────────────────────────────────────────────────────
export const adminApi = {
  users:            (params = {}) => apiClient.get('admin/users', { params }),
  getUser:          (id)          => apiClient.get(`admin/users/${id}`),
  updateUser:       (id, data)    => apiClient.put(`admin/users/${id}`, data),
  toggleUserStatus: (id, status)  => apiClient.patch(`admin/users/${id}/status`, { status }),
  deleteUser:       (id)          => apiClient.delete(`admin/users/${id}`),
  auditLogs:        (params = {}) => apiClient.get('admin/audit-logs', { params }),
  globalStats:      ()            => apiClient.get('admin/stats'),
  tenants:          ()            => apiClient.get('admin/tenants'),
  domains:          ()            => apiClient.get('admin/domains'),
  addDomain:        (data)        => apiClient.post('admin/domains', data),
  deleteDomain:     (id)          => apiClient.delete(`admin/domains/${id}`),
  // Returns users with last_login_ip field (from Cloudflare D1 audit_logs)
  userIps:          (limit = 50)  => apiClient.get('admin/users', { params: { limit, offset: 0 } }),
};

// ─────────────────────────────────────────────────────────────────────────────
// Company 3 Market Stocks & Stakes
// ─────────────────────────────────────────────────────────────────────────────
export const stockApi = {
  list:        (params = {}) => apiClient.get('stocks', { params }),
  recommended: ()            => apiClient.get('stocks/recommended'),
  lowestEver:  ()            => apiClient.get('stocks/lowest-ever'),
  executeBuy:  (data)        => apiClient.post('stocks/buy', data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Company 4 Real Estate & Tokenized Sukuk
// ─────────────────────────────────────────────────────────────────────────────
export const realEstateApi = {
  list:   (params = {}) => apiClient.get('properties', { params }),
  invest: (data)        => apiClient.post('properties/invest', data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Company 5 Developer Academy & Free Courses
// ─────────────────────────────────────────────────────────────────────────────
export const courseApi = {
  list: (params = {}) => apiClient.get('courses', { params }),
};

// ─────────────────────────────────────────────────────────────────────────────
// AI Assistants Service (Cloudflare AI & OpenAI API)
// ─────────────────────────────────────────────────────────────────────────────
export const aiApi = {
  chat:               (data) => apiClient.post('ai/chat', typeof data === 'string' ? { message: data } : data),
  bookingAssistant:   (data) => apiClient.post('ai/booking-assistant', typeof data === 'string' ? { message: data } : data),
  stockPrediction:    (data) => apiClient.post('ai/stock-prediction', typeof data === 'string' ? { query: data } : data),
  realEstateAnalyzer: (data) => apiClient.post('ai/real-estate-analyzer', typeof data === 'string' ? { query: data } : data),
  devTutor:           (data) => apiClient.post('ai/dev-tutor', typeof data === 'string' ? { question: data } : data),
};

// ─────────────────────────────────────────────────────────────────────────────
// Token management helpers (used by store)
// ─────────────────────────────────────────────────────────────────────────────
export function setToken(token) {
  localStorage.setItem('auth_token', token);
}

export function clearToken() {
  localStorage.removeItem('auth_token');
}

export function getToken() {
  return localStorage.getItem('auth_token');
}

export default apiClient;
