# 🚀 Full-Stack Website Improvement Guide
## Fawaz_ALharbi_test - Complete Step-by-Step Roadmap

**Last Updated:** April 2026  
**Tech Stack:** Vue 2 (Frontend) + Laravel 10 (Backend) + MySQL  
**Current Status:** Development Phase

---

## 📊 Executive Summary

Your project has **solid foundations** but needs improvements in:
- ✅ **Frontend:** Upgrade architecture, error handling, state management
- ✅ **Backend:** Strengthen API security, add versioning, improve error handling
- ✅ **DevOps:** Add Docker, environment configuration, CI/CD
- ✅ **Code Quality:** Add testing, better logging, validation
- ✅ **Performance:** Optimize builds, implement caching, lazy loading

---

## 🔍 PHASE 1: CODE AUDIT & ISSUES FOUND

### Frontend Issues (Vue 2)

| Issue | Severity | Impact |
|-------|----------|--------|
| ❌ Package.json has `composer` & `php` as npm deps | 🔴 CRITICAL | Build errors, confusion |
| ❌ No proper error handling in API calls | 🔴 CRITICAL | Silent failures, bad UX |
| ❌ State management lacks auth flow | 🟠 HIGH | Security risk |
| ❌ No loading states/spinners | 🟠 HIGH | Poor UX |
| ❌ Router guards incomplete | 🟠 HIGH | Unauthorized access |
| ❌ No input validation library | 🟠 HIGH | Invalid data submission |
| ❌ Missing environment configuration | 🟠 HIGH | Hardcoded URLs |
| ❌ No proper logging system | 🟡 MEDIUM | Hard to debug |
| ❌ No error boundaries | 🟡 MEDIUM | Page crashes on errors |
| ❌ Component naming inconsistent | 🟡 MEDIUM | Code maintenance |

### Backend Issues (Laravel)

| Issue | Severity | Impact |
|-------|----------|--------|
| ❌ CORS not properly configured | 🔴 CRITICAL | API access denied |
| ❌ No API versioning strategy | 🔴 CRITICAL | Breaking changes |
| ❌ Missing validation middleware | 🟠 HIGH | Invalid data accepted |
| ❌ No proper error responses | 🟠 HIGH | Inconsistent API |
| ❌ No rate limiting | 🟠 HIGH | Abuse vulnerability |
| ❌ Missing request logging | 🟡 MEDIUM | No audit trail |
| ❌ No environment variable validation | 🟡 MEDIUM | Runtime errors |

### DevOps Issues

| Issue | Severity | Impact |
|-------|----------|--------|
| ❌ No Docker setup | 🔴 CRITICAL | Deployment issues |
| ❌ No .env files | 🔴 CRITICAL | Hardcoded secrets |
| ❌ No CI/CD pipeline | 🟠 HIGH | Manual deployments |
| ❌ No proper database migrations | 🟠 HIGH | Schema inconsistency |
| ❌ No backup strategy | 🔴 CRITICAL | Data loss risk |

---

## ✅ PHASE 2: STEP-BY-STEP FIXES

### STEP 1: Fix Frontend Package.json
**Time: 5 mins | Difficulty: Easy**

#### Issue
```json
"dependencies": {
    "composer": "^4.1.0",        // ❌ Wrong!
    "php": "^1.1.0",              // ❌ Wrong!
    ...
}
```

#### Solution
Remove PHP/Composer from npm and add missing packages:

**File:** `package.json`
```json
{
  "name": "Fawaz-project",
  "version": "0.1.0",
  "private": true,
  "scripts": {
    "dev": "vue-cli-service serve",
    "serve": "vue-cli-service serve",
    "build": "vue-cli-service build",
    "build:prod": "NODE_ENV=production vue-cli-service build",
    "lint": "vue-cli-service lint",
    "lint:fix": "vue-cli-service lint --fix",
    "analyze": "vue-cli-service build --report"
  },
  "dependencies": {
    "axios": "^1.6.8",
    "core-js": "^3.8.3",
    "vee-validate": "^3.4.0",
    "vue": "^2.7.16",
    "vue-i18n": "^8.28.2",
    "vue-router": "^3.6.5",
    "vuex": "^3.6.2",
    "vuex-persist": "^3.1.3"
  },
  "devDependencies": {
    "@babel/core": "^7.12.16",
    "@babel/eslint-parser": "^7.12.16",
    "@vue/cli-plugin-babel": "~5.0.0",
    "@vue/cli-plugin-eslint": "~5.0.0",
    "@vue/cli-service": "~5.0.0",
    "compression-webpack-plugin": "^10.2.0",
    "eslint": "^7.32.0",
    "eslint-plugin-vue": "^8.0.3",
    "prettier": "^3.0.0"
  },
  "browserslist": [
    "> 1%",
    "last 2 versions",
    "not dead"
  ]
}
```

---

### STEP 2: Create Proper Environment Configuration
**Time: 10 mins | Difficulty: Easy**

#### Files to Create

**.env.example** (Frontend)
```
VUE_APP_API_URL=http://localhost:8000/api/v1
VUE_APP_API_TIMEOUT=30000
VUE_APP_DEBUG=true
VUE_APP_ENVIRONMENT=development
```

**.env.local** (Frontend - Development)
```
VUE_APP_API_URL=http://localhost:8000/api/v1
VUE_APP_API_TIMEOUT=30000
VUE_APP_DEBUG=true
VUE_APP_ENVIRONMENT=development
```

**.env.production** (Frontend - Production)
```
VUE_APP_API_URL=https://api.yourdomain.com/api/v1
VUE_APP_API_TIMEOUT=30000
VUE_APP_DEBUG=false
VUE_APP_ENVIRONMENT=production
```

---

### STEP 3: Refactor API Service Layer
**Time: 20 mins | Difficulty: Medium**

**File:** `src/services/api.js` - Complete Rewrite
```javascript
import axios from 'axios';

// ─────────────────────────────────────────────────────────────────────────────
// AXIOS INSTANCE CONFIGURATION
// ─────────────────────────────────────────────────────────────────────────────
const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8000/api/v1',
  timeout: parseInt(process.env.VUE_APP_API_TIMEOUT) || 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// ─────────────────────────────────────────────────────────────────────────────
// REQUEST INTERCEPTOR - Add Auth Token
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// ─────────────────────────────────────────────────────────────────────────────
// RESPONSE INTERCEPTOR - Handle Errors Globally
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    // Handle token expiration
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }

    // Log errors in development
    if (process.env.VUE_APP_DEBUG === 'true') {
      console.error('API Error:', {
        status: error.response?.status,
        data: error.response?.data,
        message: error.message,
      });
    }

    return Promise.reject({
      status: error.response?.status || 500,
      message: error.response?.data?.message || error.message,
      errors: error.response?.data?.errors || {},
    });
  }
);

// ─────────────────────────────────────────────────────────────────────────────
// AUTH API
// ─────────────────────────────────────────────────────────────────────────────
export const authApi = {
  login: (credentials) =>
    apiClient.post('/auth/login', credentials),

  register: (data) =>
    apiClient.post('/auth/register', data),

  logout: () =>
    apiClient.post('/auth/logout'),

  me: () =>
    apiClient.get('/auth/me'),

  refreshToken: () =>
    apiClient.post('/auth/refresh'),

  googleLogin: (id_token) =>
    apiClient.post('/auth/google', { id_token }),
};

// ─────────────────────────────────────────────────────────────────────────────
// MERCHANTS API
// ─────────────────────────────────────────────────────────────────────────────
export const merchantApi = {
  list: (params = {}) =>
    apiClient.get('/merchants', { params }),

  get: (id) =>
    apiClient.get(`/merchants/${id}`),

  create: (data) =>
    apiClient.post('/merchants', data),

  update: (id, data) =>
    apiClient.put(`/merchants/${id}`, data),

  delete: (id) =>
    apiClient.delete(`/merchants/${id}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// BOOKINGS API
// ─────────────────────────────────────────────────────────────────────────────
export const bookingApi = {
  list: (params = {}) =>
    apiClient.get('/bookings', { params }),

  get: (id) =>
    apiClient.get(`/bookings/${id}`),

  create: (data) =>
    apiClient.post('/bookings', data),

  cancel: (id) =>
    apiClient.post(`/bookings/${id}/cancel`),
};

// ─────────────────────────────────────────────────────────────────────────────
// REPORTS API
// ─────────────────────────────────────────────────────────────────────────────
export const reportApi = {
  getSales: (params = {}) =>
    apiClient.get('/reports/sales', { params }),

  getBookings: (params = {}) =>
    apiClient.get('/reports/bookings', { params }),

  getRevenue: (params = {}) =>
    apiClient.get('/reports/revenue', { params }),
};

export default apiClient;
```

---

### STEP 4: Create Proper Error Handling System
**Time: 15 mins | Difficulty: Medium**

**File:** `src/services/errorHandler.js` (NEW)
```javascript
/**
 * Centralized Error Handler
 * Converts API errors to user-friendly messages
 */

export const ERROR_MESSAGES = {
  // Network errors
  'NETWORK_ERROR': 'Unable to connect to server. Please check your internet connection.',
  'TIMEOUT': 'Request timeout. Please try again.',

  // Auth errors
  401: 'Your session has expired. Please log in again.',
  403: 'You do not have permission to perform this action.',

  // Validation errors
  422: 'Please check the form and correct any errors.',

  // Server errors
  500: 'Server error. Please try again later.',
  503: 'Service temporarily unavailable. Please try again later.',

  // Generic
  'GENERIC': 'An unexpected error occurred. Please try again.',
};

export class ApiError extends Error {
  constructor(status, message, errors = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
    this.isValidationError = status === 422;
  }
}

export function getErrorMessage(error) {
  if (error instanceof ApiError) {
    return ERROR_MESSAGES[error.status] || error.message;
  }

  if (error.message === 'Network Error') {
    return ERROR_MESSAGES.NETWORK_ERROR;
  }

  return ERROR_MESSAGES.GENERIC;
}

export function getFieldErrors(error) {
  if (error instanceof ApiError && error.isValidationError) {
    return error.errors;
  }
  return {};
}
```

---

### STEP 5: Improve Vuex Store with Authentication
**Time: 20 mins | Difficulty: Medium**

**File:** `src/store.js` - Complete Rewrite
```javascript
import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import { authApi } from './services/api';

Vue.use(Vuex);

// ─────────────────────────────────────────────────────────────────────────────
// Vuex Persistence
// ─────────────────────────────────────────────────────────────────────────────
const vuexLocal = new VuexPersist({
  key: 'vuex',
  storage: window.localStorage,
  reducer: state => ({
    auth: state.auth,
    settings: state.settings,
  }),
});

// ─────────────────────────────────────────────────────────────────────────────
// STORE
// ─────────────────────────────────────────────────────────────────────────────
export default new Vuex.Store({
  state: {
    auth: {
      token: null,
      user: null,
      isLoggedIn: false,
      isLoading: false,
      error: null,
    },
    settings: {
      locale: 'en',
      isDarkMode: false,
      sidebarCollapsed: false,
    },
    ui: {
      notifications: [],
      toasts: [],
      isLoading: false,
    },
  },

  mutations: {
    // ─── AUTH MUTATIONS ───
    SET_AUTH_LOADING(state, isLoading) {
      state.auth.isLoading = isLoading;
    },

    SET_AUTH_TOKEN(state, token) {
      state.auth.token = token;
      if (token) {
        localStorage.setItem('auth_token', token);
      } else {
        localStorage.removeItem('auth_token');
      }
    },

    SET_AUTH_USER(state, user) {
      state.auth.user = user;
      state.auth.isLoggedIn = !!user;
    },

    SET_AUTH_ERROR(state, error) {
      state.auth.error = error;
    },

    LOGOUT(state) {
      state.auth.token = null;
      state.auth.user = null;
      state.auth.isLoggedIn = false;
      state.auth.error = null;
      localStorage.removeItem('auth_token');
    },

    // ─── SETTINGS MUTATIONS ───
    SET_LOCALE(state, locale) {
      state.settings.locale = locale;
    },

    TOGGLE_DARK_MODE(state) {
      state.settings.isDarkMode = !state.settings.isDarkMode;
    },

    TOGGLE_SIDEBAR(state) {
      state.settings.sidebarCollapsed = !state.settings.sidebarCollapsed;
    },

    // ─── UI MUTATIONS ───
    ADD_TOAST(state, toast) {
      state.ui.toasts.push({
        id: Date.now(),
        ...toast,
      });
    },

    REMOVE_TOAST(state, id) {
      state.ui.toasts = state.ui.toasts.filter(t => t.id !== id);
    },

    SET_LOADING(state, isLoading) {
      state.ui.isLoading = isLoading;
    },
  },

  actions: {
    // ─── AUTH ACTIONS ───
    async login({ commit }, credentials) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);

      try {
        const response = await authApi.login(credentials);
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);
        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    async register({ commit }, formData) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);

      try {
        const response = await authApi.register(formData);
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);
        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    async fetchUser({ commit }) {
      try {
        const user = await authApi.me();
        commit('SET_AUTH_USER', user);
        return user;
      } catch (error) {
        commit('LOGOUT');
        throw error;
      }
    },

    async logout({ commit }) {
      try {
        await authApi.logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        commit('LOGOUT');
      }
    },

    // ─── SETTINGS ACTIONS ───
    setLocale({ commit }, locale) {
      commit('SET_LOCALE', locale);
    },

    toggleDarkMode({ commit }) {
      commit('TOGGLE_DARK_MODE');
    },

    toggleSidebar({ commit }) {
      commit('TOGGLE_SIDEBAR');
    },

    // ─── UI ACTIONS ───
    showToast({ commit }, toast) {
      commit('ADD_TOAST', toast);
      if (toast.autoClose !== false) {
        setTimeout(() => {
          commit('REMOVE_TOAST', toast.id);
        }, toast.duration || 3000);
      }
    },

    removeToast({ commit }, id) {
      commit('REMOVE_TOAST', id);
    },

    setLoading({ commit }, isLoading) {
      commit('SET_LOADING', isLoading);
    },
  },

  getters: {
    isAuthenticated: state => state.auth.isLoggedIn,
    currentUser: state => state.auth.user,
    authToken: state => state.auth.token,
    isAuthLoading: state => state.auth.isLoading,
    authError: state => state.auth.error,
  },

  plugins: [vuexLocal.plugin],
});
```

---

### STEP 6: Add Route Guards and Protection
**Time: 15 mins | Difficulty: Medium**

**File:** `src/router/index.js` - Add After Route Definitions
```javascript
// ─── NAVIGATION GUARDS ───
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('auth_token');
  const isPublicRoute = ['/', '/login', '/signup'].includes(to.path);

  // Redirect to login if accessing protected route without token
  if (!isPublicRoute && !isLoggedIn) {
    return next('/login');
  }

  // Redirect to dashboard if trying to access login/signup while authenticated
  if (isLoggedIn && to.path === '/login') {
    return next('/dashboard');
  }

  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title;
  }

  next();
});

router.afterEach(() => {
  // Scroll to top after each route change
  window.scrollTo(0, 0);
});
```

---

### STEP 7: Create Input Validation Service
**Time: 15 mins | Difficulty: Medium**

**File:** `src/services/validators.js` (NEW)
```javascript
/**
 * Form Validation Rules
 */

export const validationRules = {
  email: [
    { required: true, message: 'Email is required' },
    { type: 'email', message: 'Please enter a valid email' },
  ],

  password: [
    { required: true, message: 'Password is required' },
    { min: 8, message: 'Password must be at least 8 characters' },
    {
      pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
      message: 'Password must contain uppercase, lowercase, and numbers',
    },
  ],

  name: [
    { required: true, message: 'Name is required' },
    { min: 3, message: 'Name must be at least 3 characters' },
    { max: 100, message: 'Name must not exceed 100 characters' },
  ],

  phone: [
    { required: true, message: 'Phone is required' },
    { pattern: /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/, message: 'Invalid phone format' },
  ],

  url: [
    { type: 'url', message: 'Please enter a valid URL' },
  ],
};

/**
 * Validate a single field
 */
export function validateField(value, rules) {
  for (const rule of rules) {
    if (rule.required && (!value || value.trim() === '')) {
      return rule.message;
    }

    if (value && rule.min && value.length < rule.min) {
      return rule.message;
    }

    if (value && rule.max && value.length > rule.max) {
      return rule.message;
    }

    if (value && rule.pattern && !rule.pattern.test(value)) {
      return rule.message;
    }

    if (value && rule.type === 'email') {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(value)) {
        return rule.message;
      }
    }

    if (value && rule.type === 'url') {
      try {
        new URL(value);
      } catch {
        return rule.message;
      }
    }
  }

  return null;
}

/**
 * Validate entire form
 */
export function validateForm(formData, rules) {
  const errors = {};

  for (const [field, fieldRules] of Object.entries(rules)) {
    const error = validateField(formData[field], fieldRules);
    if (error) {
      errors[field] = error;
    }
  }

  return errors;
}
```

---

### STEP 8: Fix Backend CORS Configuration
**Time: 10 mins | Difficulty: Easy**

**Backend File:** `backend-laravel/config/cors.php`
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1:8080',
        // Add production domains:
        // 'https://yourdomain.com',
        // 'https://app.yourdomain.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

---

### STEP 9: Create API Response Wrapper (Backend)
**Time: 20 mins | Difficulty: Medium**

**Backend File:** `backend-laravel/app/Traits/ApiResponse.php` (NEW)
```php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return success response
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return validation error
     */
    protected function validationErrorResponse($errors): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($data, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->from(),
                'to' => $data->to(),
            ],
        ], 200);
    }
}
```

---

### STEP 10: Add Request Validation Middleware (Backend)
**Time: 15 mins | Difficulty: Medium**

**Backend File:** `backend-laravel/app/Http/Middleware/ValidateJsonRequest.php` (NEW)
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateJsonRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Only for API requests
        if ($request->is('api/*')) {
            // Validate Content-Type
            if ($request->getMethod() !== 'GET' && 
                !$request->headers->has('Content-Type') ||
                !str_contains($request->headers->get('Content-Type'), 'application/json')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Content-Type must be application/json',
                ], 415);
            }
        }

        return $next($request);
    }
}
```

---

### STEP 11: Create Error Handler Exception (Backend)
**Time: 15 mins | Difficulty: Medium**

**Backend File:** `backend-laravel/app/Exceptions/Handler.php` - Update
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // JSON API errors
        if ($request->expectsJson()) {
            return $this->handleJsonException($exception);
        }

        return parent::render($request, $exception);
    }

    private function handleJsonException(Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found',
            ], 404);
        }

        // Default error
        $statusCode = $exception->getCode() ?: 500;
        $message = config('app.debug') ? $exception->getMessage() : 'Server error';

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
```

---

### STEP 12: Create Docker Setup
**Time: 25 mins | Difficulty: Medium**

**File:** `Dockerfile` (NEW)
```dockerfile
FROM php:8.1-fpm

WORKDIR /app

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy backend files
COPY backend-laravel/ .

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /app

CMD ["php-fpm"]
```

**File:** `docker-compose.yml` (NEW)
```yaml
version: '3.8'

services:
  web:
    build: .
    container_name: fawaz_app
    working_dir: /app
    ports:
      - "8000:8000"
    volumes:
      - ./backend-laravel:/app
    environment:
      - DB_HOST=db
      - DB_NAME=${DB_NAME:-fawaz_db}
      - DB_USER=${DB_USER:-root}
      - DB_PASSWORD=${DB_PASSWORD:-password}
    depends_on:
      - db

  db:
    image: mysql:8.0
    container_name: fawaz_db
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_PASSWORD:-password}
      - MYSQL_DATABASE=${DB_NAME:-fawaz_db}
      - MYSQL_USER=${DB_USER:-root}
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/1.sql
      - ./database/seeds.sql:/docker-entrypoint-initdb.d/2.sql

  frontend:
    image: node:18
    container_name: fawaz_frontend
    working_dir: /app
    command: npm run serve
    ports:
      - "8080:8080"
    volumes:
      - ./:/app
    environment:
      - VUE_APP_API_URL=http://localhost:8000/api/v1

volumes:
  db_data:
```

**File:** `.env.example` (Backend)
```
APP_NAME=Fawaz
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=fawaz_db
DB_USERNAME=root
DB_PASSWORD=password

CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_DRIVER=sync

LOG_CHANNEL=stack
```

---

## 🔧 PHASE 3: ADVANCED IMPROVEMENTS

### Step 13: Add Request Logging
**File:** `src/services/logger.js` (NEW)
```javascript
/**
 * Client-side logger
 */

const LOG_LEVELS = {
  DEBUG: 0,
  INFO: 1,
  WARN: 2,
  ERROR: 3,
};

class Logger {
  constructor(level = 'DEBUG') {
    this.level = LOG_LEVELS[level] || LOG_LEVELS.DEBUG;
  }

  debug(message, data = {}) {
    this._log('DEBUG', message, data, 'log');
  }

  info(message, data = {}) {
    this._log('INFO', message, data, 'info');
  }

  warn(message, data = {}) {
    this._log('WARN', message, data, 'warn');
  }

  error(message, data = {}) {
    this._log('ERROR', message, data, 'error');
  }

  _log(level, message, data, method) {
    if (LOG_LEVELS[level] < this.level) return;

    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${level}] ${message}`;

    console[method](logMessage, data);

    // Send to analytics/monitoring in production
    if (process.env.VUE_APP_ENVIRONMENT === 'production') {
      this._sendToServer(level, message, data);
    }
  }

  _sendToServer(level, message, data) {
    // TODO: Implement analytics tracking
  }
}

export default new Logger(process.env.VUE_APP_DEBUG === 'true' ? 'DEBUG' : 'WARN');
```

### Step 14: Add Loading State Component
**File:** `src/components/LoadingSpinner.vue` (NEW)
```vue
<template>
  <div class="spinner-container">
    <div class="spinner"></div>
    <p class="spinner-text">{{ message }}</p>
  </div>
</template>

<script>
export default {
  props: {
    message: {
      type: String,
      default: 'Loading...'
    }
  }
};
</script>

<style scoped>
.spinner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.spinner-text {
  color: #666;
  font-size: 14px;
}
</style>
```

### Step 15: Add Toast Notification Component
**File:** `src/components/ToastNotification.vue` (NEW)
```vue
<template>
  <div class="toast-container">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="['toast', `toast-${toast.type}`]"
      >
        <span>{{ toast.message }}</span>
        <button @click="removeToast(toast.id)" class="toast-close">×</button>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['ui']),
    toasts() {
      return this.ui && this.ui.toasts ? this.ui.toasts : [];
    }
  },
  methods: {
    ...mapActions(['removeToast'])
  }
};
</script>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 400px;
}

.toast {
  background: white;
  border-radius: 4px;
  padding: 16px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  animation: slideIn 0.3s ease;
}

.toast-success {
  border-left: 4px solid #27ae60;
}

.toast-error {
  border-left: 4px solid #e74c3c;
}

.toast-warning {
  border-left: 4px solid #f39c12;
}

.toast-info {
  border-left: 4px solid #3498db;
}

.toast-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
  margin-left: 10px;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s;
}

.toast-exit-to {
  transform: translateX(400px);
  opacity: 0;
}
</style>
```

---

## 📋 PHASE 4: EXECUTION CHECKLIST

### Frontend Fixes - Priority Order
- [ ] Fix package.json (CRITICAL - Step 1)
- [ ] Add environment config files (Step 2)
- [ ] Refactor API service (Step 3)
- [ ] Create error handler (Step 4)
- [ ] Update Vuex store (Step 5)
- [ ] Add route guards (Step 6)
- [ ] Add validation service (Step 7)
- [ ] Create spinner component (Step 14)
- [ ] Create toast component (Step 15)

### Backend Fixes - Priority Order
- [ ] Fix CORS config (Step 8)
- [ ] Create response wrapper (Step 9)
- [ ] Add request validation (Step 10)
- [ ] Update exception handler (Step 11)

### DevOps - Priority Order
- [ ] Create Docker setup (Step 12)
- [ ] Create environment files (Step 2)

### Testing & Deployment
- [ ] [ ] Run `npm install` to verify dependencies
- [ ] [ ] Test API connectivity
- [ ] [ ] Test authentication flow
- [ ] [ ] Test CORS
- [ ] [ ] Deploy to Docker
- [ ] [ ] Run database migrations

---

## 🎯 PERFORMANCE OPTIMIZATIONS

### 1. Code Splitting
```javascript
// Already implemented in router with lazy-loading
const C2Home = () => import('../components/company2/pages/C2Home.vue');
```

### 2. Compression
Add to `vue.config.js`:
```javascript
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  configureWebpack: {
    plugins: [
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.js$|\.css$|\.html$/,
        threshold: 10240,
        minRatio: 0.8,
      })
    ]
  }
};
```

### 3. Caching Headers
Add to Laravel routes:
```php
Route::middleware(['cache:3600'])->group(function () {
    Route::get('/static-data', 'Controller@getData');
});
```

---

## 🚀 DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] Environment variables configured
- [ ] Database backed up
- [ ] Tests passing
- [ ] Build successful (`npm run build:prod`)
- [ ] No console errors

### Deployment
- [ ] Pull latest code
- [ ] Install dependencies
- [ ] Run migrations
- [ ] Clear caches
- [ ] Build frontend
- [ ] Deploy Docker containers

### Post-Deployment
- [ ] Verify API endpoints
- [ ] Test authentication
- [ ] Check logs
- [ ] Monitor errors

---

## 📞 SUPPORT & NEXT STEPS

**Immediate Actions (This Week):**
1. Fix package.json
2. Create environment configs
3. Refactor API service
4. Update store with auth

**This Sprint (Next 2 Weeks):**
1. Add error handling
2. Create Docker setup
3. Fix CORS
4. Add input validation

**Next Phase (4+ Weeks):**
1. Upgrade Vue 2 → Vue 3
2. Add unit/E2E tests
3. Implement CI/CD
4. Add monitoring
5. Performance optimization

---

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Status:** Ready for Implementation
