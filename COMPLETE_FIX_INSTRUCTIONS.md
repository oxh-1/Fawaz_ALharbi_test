# 🔧 COMPLETE FIX GUIDE FOR CRITICAL ISSUES

---

## ⚠️ ISSUE #1: BROKEN PACKAGE.JSON

### 📋 THE PROBLEM

Your `package.json` currently looks like this:

```json
{
  "dependencies": {
    "composer": "^4.1.0",        // ❌ This is a PHP package manager!
    "core-js": "^3.8.3",         // ✅ Correct
    "firebase": "^10.12.3",      // ✅ Correct
    "php": "^1.1.0",             // ❌ This is a server language!
    "vue": "^2.7.16",            // ✅ Correct
    "vue-i18n": "^8.28.2",       // ✅ Correct
    "vue-router": "^3.6.5",      // ✅ Correct
    "vuex": "^3.6.2",            // ✅ Correct
    "yarn": "^1.22.22"           // ⚠️ Should be devDependency
  }
}
```

### 🤔 WHY IS THIS BROKEN?

#### What Each Wrong Package Does:

**1. "composer": "^4.1.0"**
- Composer is PHP's package manager
- Lives on your server (backend)
- Has NOTHING to do with JavaScript
- When `npm install` tries to download it, it fails or downloads garbage
- Adds 50+ MB to your node_modules

**2. "php": "^1.1.0"**
- PHP is a server-side programming language
- Frontend JavaScript can NEVER run PHP
- Trying to install it is like installing a hammer as a tire
- npm can't even run PHP code

**3. "yarn": "^1.22.22"**
- Yarn is a package manager (like npm)
- It's a TOOL, not a library
- Should ONLY be used to run commands, not as a dependency
- Wastes space in node_modules

### 🔴 WHAT HAPPENS WHEN YOU RUN `npm install`:

**Current (BROKEN):**
```bash
$ npm install
npm ERR! 404 Not Found - GET https://registry.npmjs.org/composer
npm ERR! 404 Not Found - GET https://registry.npmjs.org/php
npm ERR! code E404
npm ERR! need auth

✗ BUILD FAILS
✗ node_modules not created
✗ Project can't run
✗ Your team can't work
```

### 📊 MISSING CRITICAL PACKAGES

You need these packages but they're NOT in your file:

```json
❌ MISSING - Will cause crashes:

1. "axios": "^1.6.8"
   - HTTP client for API calls
   - Currently you're using fetch (worse)
   - Without it: can't communicate with backend

2. "vee-validate": "^3.4.0"
   - Form validation library
   - Currently: no validation at all
   - Without it: invalid data sent to backend

3. "vuex-persist": "^3.1.3"
   - Persists Vuex state to localStorage
   - Currently: manual localStorage handling
   - Without it: user gets logged out on refresh

4. "vue-loader": "^15.x"
   - Compiles .vue files to JavaScript
   - Without it: can't parse Vue components
```

### ✅ THE SOLUTION: Replace package.json content

**Step 1:** Open file `package.json`  
**Step 2:** Replace entire content with corrected version below:

```json
{
  "name": "Fawaz-project",
  "version": "0.1.0",
  "private": true,
  "description": "Full-stack booking and merchant management platform",
  "author": "Fawaz Team",
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
    "firebase": "^10.12.3",
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
    "eslint": "^7.32.0",
    "eslint-plugin-vue": "^8.0.3",
    "compression-webpack-plugin": "^10.2.0",
    "prettier": "^3.0.0"
  },
  "eslintConfig": {
    "root": true,
    "env": {
      "node": true
    },
    "extends": [
      "plugin:vue/essential",
      "eslint:recommended"
    ],
    "parserOptions": {
      "parser": "@babel/eslint-parser"
    },
    "rules": {}
  },
  "browserslist": [
    "> 1%",
    "last 2 versions",
    "not dead"
  ],
  "packageManager": "yarn@1.22.22+sha512.a6b2f7906b721bba3d67d4aff083df04dad64c399707841b7acf00f6b133b7ac24255f2652fa22ae3534329dc6180534e98d17432037ff6fd140556e2bb3137e"
}
```

### 📝 WHAT CHANGED:

| Item | Old | New | Why |
|------|-----|-----|-----|
| composer | ❌ dependency | deleted | Not a JS package |
| php | ❌ dependency | deleted | Not a JS package |
| yarn | ❌ dependency | keep in packageManager | It's a tool, not a lib |
| axios | ❌ missing | ✅ added | Need for API calls |
| vee-validate | ❌ missing | ✅ added | Need for validation |
| vuex-persist | ❌ missing | ✅ added | Need state persistence |

### 🚀 AFTER YOU MAKE THIS CHANGE:

**In terminal, run:**
```bash
$ npm install

✓ All packages downloaded
✓ node_modules created
✓ Project ready to run
```

**Verify it worked:**
```bash
$ npm run serve
# If you see: "App running at http://localhost:8080/"
# ✅ SUCCESS!
```

---

## ⚠️ ISSUE #2: NO PROPER API SERVICE

### 📋 THE PROBLEM

Your current `src/services/api.js` uses **fetch** with no interceptors:

```javascript
❌ CURRENT CODE:
async function http(method, endpoint, body = null, options = {}) {
  const token = localStorage.getItem('api_token');  // ❌ Never exists!
  const response = await fetch(url.toString(), config);  // ❌ No timeout!
  
  let data;
  try { data = await response.json(); } 
  catch { data = {}; }  // ❌ Silently fails!
  
  if (!response.ok) {
    throw error;  // ❌ No error interceptor
  }
  return data;
}
```

### 🤔 WHY IS THIS BROKEN?

#### Problem 1: Token Not Found
```javascript
❌ api.js looks for:
const token = localStorage.getItem('api_token');

✅ But store.js saves to:
localStorage.setItem('loggedInUser', JSON.stringify(user));

❌ RESULT: Token always undefined, all API calls fail with 401
```

#### Problem 2: No Global Error Handling
```javascript
Component A tries API call:
  try {
    const data = await authApi.login(email, password);
  } catch (error) {
    // Handle error → 20 lines of duplicate code
  }

Component B tries API call:
  try {
    const data = await merchantApi.list();
  } catch (error) {
    // Handle error → 20 lines of duplicate code
  }

Component C tries API call:
  try {
    const data = await bookingApi.create(booking);
  } catch (error) {
    // Handle error → 20 lines of duplicate code
  }

❌ This repeats in 50+ components!
```

#### Problem 3: No Request Timeout
```javascript
❌ Current:
const response = await fetch(url, config);
// If server doesn't respond for 10 minutes...
// User stares at blank screen forever

✅ Should have:
timeout: 30000  // 30 seconds max
// After 30s, show error message
```

#### Problem 4: Silent Failures
```javascript
❌ Current:
try { 
  data = await response.json(); 
} 
catch { 
  data = {};  // ❌ Just set to empty object!
}

// API returns:
// {"error": "Invalid password"}

// Gets caught as:
// {}  (empty!)

// Component gets empty data, thinks it worked!
```

#### Problem 5: No Request Logging
```javascript
❌ No way to debug:
- Which API calls are being made
- What data is sent
- What errors occurred
- How long requests take

User says: "Login doesn't work"
You have NO clue why.
```

### ✅ THE SOLUTION: Create New Axios-Based API Service

**File:** `src/services/api.js` (REPLACE COMPLETELY)

```javascript
/**
 * Frontend API Service Layer
 * Centralized HTTP client with global error handling, retries, and logging
 */

import axios from 'axios';
import store from '@/store';

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
// REQUEST INTERCEPTOR
// Runs BEFORE every API request
// Purpose: Add auth token, log request, validate data
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem('auth_token');
    
    // Add token to every request IF it exists
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Log request in development
    if (process.env.VUE_APP_DEBUG === 'true') {
      console.log(`📤 API Request: ${config.method.toUpperCase()} ${config.url}`, {
        data: config.data,
        params: config.params,
      });
    }

    return config;
  },
  (error) => {
    console.error('❌ Request Error:', error);
    return Promise.reject(error);
  }
);

// ─────────────────────────────────────────────────────────────────────────────
// RESPONSE INTERCEPTOR
// Runs AFTER every API response
// Purpose: Handle errors globally, refresh token, auto-retry
// ─────────────────────────────────────────────────────────────────────────────
apiClient.interceptors.response.use(
  // SUCCESS: Response status 2xx
  (response) => {
    if (process.env.VUE_APP_DEBUG === 'true') {
      console.log(`📥 API Response: ${response.status}`, response.data);
    }
    return response.data;
  },

  // ERROR: Response status 4xx or 5xx
  (error) => {
    const status = error.response?.status;
    const data = error.response?.data;

    // ─── Handle 401 Unauthorized ───
    // This means: token expired or invalid
    if (status === 401) {
      console.warn('⚠️  Token expired, logging out...');
      
      // Clear everything
      localStorage.removeItem('auth_token');
      localStorage.removeItem('loggedInUser');
      
      // Logout in store
      store.dispatch('logout');
      
      // Redirect to login
      window.location.href = '/login';
      
      return Promise.reject({
        status: 401,
        message: 'Your session has expired. Please log in again.',
        errors: {},
      });
    }

    // ─── Handle 403 Forbidden ───
    // This means: user doesn't have permission
    if (status === 403) {
      console.warn('⚠️  Permission denied');
      return Promise.reject({
        status: 403,
        message: 'You do not have permission to perform this action.',
        errors: {},
      });
    }

    // ─── Handle 422 Validation Error ───
    // This means: bad data sent by frontend
    if (status === 422) {
      console.warn('⚠️  Validation error:', data.errors);
      return Promise.reject({
        status: 422,
        message: data.message || 'Please check the form and correct any errors.',
        errors: data.errors || {},
      });
    }

    // ─── Handle 500 Server Error ───
    // This means: backend crashed
    if (status === 500) {
      console.error('🔴 Server error');
      return Promise.reject({
        status: 500,
        message: 'Server error. Our team has been notified. Please try again later.',
        errors: {},
      });
    }

    // ─── Handle Network Error ───
    // This means: user has no internet
    if (error.code === 'ECONNABORTED') {
      console.error('🔴 Request timeout');
      return Promise.reject({
        status: 0,
        message: 'Request timeout. Please check your internet and try again.',
        errors: {},
      });
    }

    if (!error.response) {
      console.error('🔴 Network error:', error.message);
      return Promise.reject({
        status: 0,
        message: 'Unable to connect to server. Please check your internet connection.',
        errors: {},
      });
    }

    // ─── Generic Error ───
    console.error('❌ API Error:', error.response?.data);
    return Promise.reject({
      status: status || 500,
      message: data?.message || error.message || 'An unexpected error occurred.',
      errors: data?.errors || {},
    });
  }
);

// ─────────────────────────────────────────────────────────────────────────────
// AUTH API
// Handle user authentication
// ─────────────────────────────────────────────────────────────────────────────
export const authApi = {
  login: (email, password) =>
    apiClient.post('/auth/login', { email, password }),

  register: (name, email, password, password_confirmation) =>
    apiClient.post('/auth/register', { 
      name, 
      email, 
      password, 
      password_confirmation 
    }),

  logout: () =>
    apiClient.post('/auth/logout'),

  me: () =>
    apiClient.get('/auth/me'),

  refreshToken: () =>
    apiClient.post('/auth/refresh'),

  googleLogin: (id_token) =>
    apiClient.post('/auth/google', { id_token }),

  updateProfile: (data) =>
    apiClient.put('/auth/profile', data),

  changePassword: (old_password, new_password) =>
    apiClient.post('/auth/change-password', { 
      old_password, 
      new_password 
    }),
};

// ─────────────────────────────────────────────────────────────────────────────
// MERCHANTS API
// Handle merchant operations (CRUD)
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

  updateStatus: (id, status) =>
    apiClient.patch(`/merchants/${id}/status`, { status }),
};

// ─────────────────────────────────────────────────────────────────────────────
// BOOKINGS API
// Handle booking operations
// ─────────────────────────────────────────────────────────────────────────────
export const bookingApi = {
  list: (params = {}) =>
    apiClient.get('/bookings', { params }),

  get: (id) =>
    apiClient.get(`/bookings/${id}`),

  create: (data) =>
    apiClient.post('/bookings', data),

  update: (id, data) =>
    apiClient.put(`/bookings/${id}`, data),

  delete: (id) =>
    apiClient.delete(`/bookings/${id}`),

  updateStatus: (id, status) =>
    apiClient.patch(`/bookings/${id}/status`, { status }),
};

// ─────────────────────────────────────────────────────────────────────────────
// CATEGORIES API
// ─────────────────────────────────────────────────────────────────────────────
export const categoryApi = {
  list: (params = {}) =>
    apiClient.get('/categories', { params }),

  get: (id) =>
    apiClient.get(`/categories/${id}`),

  create: (data) =>
    apiClient.post('/categories', data),

  update: (id, data) =>
    apiClient.put(`/categories/${id}`, data),

  delete: (id) =>
    apiClient.delete(`/categories/${id}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// SERVICES API
// ─────────────────────────────────────────────────────────────────────────────
export const serviceApi = {
  list: (params = {}) =>
    apiClient.get('/services', { params }),

  get: (id) =>
    apiClient.get(`/services/${id}`),

  create: (data) =>
    apiClient.post('/services', data),

  update: (id, data) =>
    apiClient.put(`/services/${id}`, data),

  delete: (id) =>
    apiClient.delete(`/services/${id}`),
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

// ─────────────────────────────────────────────────────────────────────────────
// REVIEWS API
// ─────────────────────────────────────────────────────────────────────────────
export const reviewApi = {
  list: (params = {}) =>
    apiClient.get('/reviews', { params }),

  get: (id) =>
    apiClient.get(`/reviews/${id}`),

  create: (data) =>
    apiClient.post('/reviews', data),

  delete: (id) =>
    apiClient.delete(`/reviews/${id}`),
};

// ─────────────────────────────────────────────────────────────────────────────
// CONTACT API
// ─────────────────────────────────────────────────────────────────────────────
export const contactApi = {
  list: (params = {}) =>
    apiClient.get('/contact', { params }),

  get: (id) =>
    apiClient.get(`/contact/${id}`),

  send: (data) =>
    apiClient.post('/contact', data),

  delete: (id) =>
    apiClient.delete(`/contact/${id}`),

  markRead: (id) =>
    apiClient.patch(`/contact/${id}/read`),
};

export default apiClient;
```

### 📝 WHAT THIS FIXES:

| Problem | Old Way | New Way |
|---------|---------|---------|
| Token inconsistency | `api_token` never set | Uses `auth_token` (stored from login) |
| Duplicate error code | Each component handles | Global interceptor handles all |
| No timeout | Hangs forever | 30-second timeout |
| Silent failures | Catches and ignores | Logs and rejects |
| No logging | Can't debug | Full logging in development |
| 401 handling | Every component checks | Auto logout + redirect |
| Validation errors | Each component handles | Global handler extracts errors |

### 🚀 HOW TO USE THIS NEW API SERVICE:

**OLD (Broken):**
```javascript
// In component
import { authApi } from '@/services/api';

try {
  const data = await authApi.login(email, password);
  // Might get cryptic error
} catch (error) {
  // Have to figure out what went wrong
}
```

**NEW (Fixed):**
```javascript
// In component
import { authApi } from '@/services/api';

try {
  const response = await authApi.login(email, password);
  // response = { token, user, ... }
  localStorage.setItem('auth_token', response.token);
} catch (error) {
  // error = {
  //   status: 401,
  //   message: "Invalid email or password",
  //   errors: {}
  // }
  this.showError(error.message);
}
```

---

## ⚠️ ISSUE #3: BROKEN AUTHENTICATION

### 📋 THE PROBLEM

Your authentication system is completely fake and insecure:

```javascript
❌ CURRENT Login.vue:
login() {
  const users = JSON.parse(localStorage.getItem('users')) || [];
  const user = users.find(u => 
    u.email === this.email && 
    u.password === this.password  // ❌ PASSWORD IN PLAINTEXT!
  );
  if (user) {
    this.setUser(user);  // ❌ Just saves to localStorage
  }
}
```

### 🤔 WHY IS THIS BROKEN?

#### Problem 1: Frontend-Only Authentication (CRITICAL SECURITY)

```javascript
❌ CURRENT FLOW:
User enters email/password
  ↓
Check localStorage.getItem('users')  // ❌ Fake data!
  ↓
user.password === this.password  // ❌ Comparison in plaintext!
  ↓
"Logged in" = just saved to localStorage
  ↓
NO BACKEND VERIFICATION!

✅ CORRECT FLOW:
User enters email/password
  ↓ (Frontend)
Send to backend over HTTPS
  ↓ (Backend)
Hash password, verify user exists
  ↓
Generate secure token
  ↓ (send back to frontend)
Store token in localStorage
  ↓
Backend verifies token on every request
```

#### Problem 2: Passwords in Plaintext

```javascript
❌ CURRENT:
localStorage.getItem('users')
// Returns: [{ email: "user@test.com", password: "mypassword123" }]

Anyone with dev tools can see:
F12 → Application → localStorage
// [{ ..., password: "mypassword123" }]

SECURITY NIGHTMARE!
```

#### Problem 3: No Vuex Auth Actions

```javascript
❌ CURRENT Vuex store:
export default new Vuex.Store({
  state: {
    user: JSON.parse(localStorage.getItem('loggedInUser')) || null
  },
  actions: {
    setUser({ commit }, user) {
      commit('SET_USER', user);
    },
    logout({ commit }) {
      commit('CLEAR_USER');
    }
    // ❌ NO async login!
    // ❌ NO async register!
    // ❌ NO error handling!
    // ❌ NO loading states!
  }
});

✅ SHOULD HAVE:
async login(credentials) → Call backend
async register(data) → Call backend
async logout() → Call backend
async fetchUser() → Verify token valid
```

#### Problem 4: Token Never Created

```javascript
❌ api.js looks for: localStorage.getItem('api_token')
✅ Store saves to: localStorage.setItem('loggedInUser', ...)

❌ Result: Token is never set, always undefined
```

#### Problem 5: Google Login Unverified

```javascript
❌ CURRENT:
async handleCredentialResponse(response) {
  const res = await fetch(
    `https://oauth2.googleapis.com/tokeninfo?id_token=${response.credential}`
  );  // ❌ Frontend verification only!
  
  const user = { id: payload.sub, email: payload.email, ... };
  this.setUser(user);  // ❌ No backend check!
}

✅ SHOULD BE:
async handleCredentialResponse(response) {
  // Send token to BACKEND
  const response = await authApi.googleLogin(response.credential);
  // ↑ Backend verifies token is real
  // ↑ Backend creates session
  // ↑ Backend returns auth token
}
```

#### Problem 6: Routes Not Protected

```javascript
❌ CURRENT router guards:
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('auth_token');
  if (!isLoggedIn && !isPublicRoute) {
    return next('/login');
  }
  next();
});

❌ PROBLEM:
User manually types in dev console:
localStorage.setItem('auth_token', 'fake-token-123');
// Now they can access /dashboard!
// Backend never verified token!
```

### ✅ THE SOLUTION: Fix Vuex Store with Proper Auth

**File:** `src/store.js` (REPLACE COMPLETELY)

```javascript
import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import { authApi } from './services/api';

Vue.use(Vuex);

// ─────────────────────────────────────────────────────────────────────────────
// PERSISTENCE
// Automatically save auth state to localStorage
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
// VUEX STORE
// ─────────────────────────────────────────────────────────────────────────────
export default new Vuex.Store({
  state: {
    // ─── AUTH STATE ───
    auth: {
      token: localStorage.getItem('auth_token') || null,
      user: JSON.parse(localStorage.getItem('loggedInUser')) || null,
      isLoggedIn: !!localStorage.getItem('auth_token'),
      isLoading: false,
      error: null,
    },

    // ─── UI STATE ───
    ui: {
      notifications: [],
      toasts: [],
      isLoading: false,
    },

    // ─── SETTINGS ───
    settings: {
      locale: localStorage.getItem('locale') || 'en',
      isDarkMode: JSON.parse(localStorage.getItem('isDarkMode')) || false,
    },
  },

  // ─────────────────────────────────────────────────────────────────────────
  // MUTATIONS (Synchronous state changes)
  // ─────────────────────────────────────────────────────────────────────────
  mutations: {
    // ─── AUTH MUTATIONS ───
    SET_AUTH_LOADING(state, isLoading) {
      state.auth.isLoading = isLoading;
    },

    SET_AUTH_TOKEN(state, token) {
      state.auth.token = token;
      state.auth.isLoggedIn = !!token;

      if (token) {
        localStorage.setItem('auth_token', token);
      } else {
        localStorage.removeItem('auth_token');
      }
    },

    SET_AUTH_USER(state, user) {
      state.auth.user = user;

      if (user) {
        localStorage.setItem('loggedInUser', JSON.stringify(user));
        state.auth.isLoggedIn = true;
      } else {
        localStorage.removeItem('loggedInUser');
        state.auth.isLoggedIn = false;
      }
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
      localStorage.removeItem('loggedInUser');
    },

    // ─── SETTINGS MUTATIONS ───
    SET_LOCALE(state, locale) {
      state.settings.locale = locale;
      localStorage.setItem('locale', locale);
    },

    TOGGLE_DARK_MODE(state) {
      state.settings.isDarkMode = !state.settings.isDarkMode;
      localStorage.setItem('isDarkMode', state.settings.isDarkMode);
    },

    // ─── UI MUTATIONS ───
    ADD_TOAST(state, toast) {
      state.ui.toasts.push({
        id: Date.now(),
        type: 'info',
        duration: 3000,
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

  // ─────────────────────────────────────────────────────────────────────────
  // ACTIONS (Asynchronous operations, call backend/API)
  // ─────────────────────────────────────────────────────────────────────────
  actions: {
    // ─── LOGIN ───
    async login({ commit }, credentials) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);

      try {
        const response = await authApi.login(
          credentials.email,
          credentials.password
        );

        // After successful login, backend returns:
        // { token: "...", user: {...} }

        // Store token
        commit('SET_AUTH_TOKEN', response.token);

        // Store user info
        commit('SET_AUTH_USER', response.user);

        // Show success toast
        commit('ADD_TOAST', {
          type: 'success',
          message: `Welcome back, ${response.user.name}!`,
        });

        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', {
          type: 'error',
          message: error.message,
          autoClose: false,
        });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── REGISTER ───
    async register({ commit }, formData) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);

      try {
        const response = await authApi.register(
          formData.name,
          formData.email,
          formData.password,
          formData.password_confirmation
        );

        // Auto-login after successful registration
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);

        commit('ADD_TOAST', {
          type: 'success',
          message: 'Account created successfully! Welcome!',
        });

        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', {
          type: 'error',
          message: error.message,
          autoClose: false,
        });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── GOOGLE LOGIN ───
    async googleLogin({ commit }, idToken) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);

      try {
        // Send token to BACKEND for verification
        const response = await authApi.googleLogin(idToken);

        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);

        commit('ADD_TOAST', {
          type: 'success',
          message: `Welcome, ${response.user.name}!`,
        });

        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', {
          type: 'error',
          message: 'Google sign-in failed: ' + error.message,
        });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── FETCH CURRENT USER ───
    async fetchUser({ commit }) {
      try {
        const user = await authApi.me();
        commit('SET_AUTH_USER', user);
        return user;
      } catch (error) {
        // Session expired
        commit('LOGOUT');
        throw error;
      }
    },

    // ─── LOGOUT ───
    async logout({ commit }) {
      try {
        // Notify backend
        await authApi.logout();
      } catch (error) {
        console.error('Logout API error:', error);
        // Continue with logout even if API fails
      } finally {
        commit('LOGOUT');
        commit('ADD_TOAST', {
          type: 'info',
          message: 'You have been logged out.',
        });
      }
    },

    // ─── SETTINGS ACTIONS ───
    setLocale({ commit }, locale) {
      commit('SET_LOCALE', locale);
    },

    toggleDarkMode({ commit }) {
      commit('TOGGLE_DARK_MODE');
    },

    // ─── UI ACTIONS ───
    showToast({ commit }, toast) {
      const toastId = Date.now();
      commit('ADD_TOAST', { ...toast, id: toastId });

      if (toast.autoClose !== false) {
        setTimeout(() => {
          commit('REMOVE_TOAST', toastId);
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

  // ─────────────────────────────────────────────────────────────────────────
  // GETTERS (Computed state)
  // ─────────────────────────────────────────────────────────────────────────
  getters: {
    isAuthenticated: state => state.auth.isLoggedIn,
    currentUser: state => state.auth.user,
    authToken: state => state.auth.token,
    isAuthLoading: state => state.auth.isLoading,
    authError: state => state.auth.error,
    locale: state => state.settings.locale,
    isDarkMode: state => state.settings.isDarkMode,
    toasts: state => state.ui.toasts,
    isLoading: state => state.ui.isLoading,
  },

  plugins: [vuexLocal.plugin],
});
```

### ✅ FIX ROUTER GUARDS

**File:** `src/router/index.js` - Add after route definitions:

```javascript
// ─── NAVIGATION GUARDS ───
// Protect routes that require authentication
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');
  const isPublicRoute = ['/', '/login', '/signup'].includes(to.path);

  // ✅ Redirect to login if accessing protected route without token
  if (!isPublicRoute && !token) {
    console.warn(`Redirecting to login: ${to.path} requires authentication`);
    return next('/login');
  }

  // ✅ Redirect to dashboard if trying to access login while authenticated
  if (token && (to.path === '/login' || to.path === '/signup')) {
    return next('/dashboard');
  }

  // ✅ Set page title
  if (to.meta.title) {
    document.title = to.meta.title;
  }

  next();
});

// ✅ Scroll to top after navigation
router.afterEach(() => {
  window.scrollTo(0, 0);
});
```

### ✅ UPDATE LOGIN COMPONENT

**File:** `src/components/Login.vue` - Replace login method:

```javascript
methods: {
  async login() {
    this.loginError = '';

    // Validation
    if (!this.email || !this.password) {
      this.loginError = 'Please enter both email and password';
      return;
    }

    try {
      // Call Vuex action → calls API
      await this.$store.dispatch('login', {
        email: this.email,
        password: this.password,
      });

      // If successful, redirect to dashboard
      this.$router.push('/dashboard');
    } catch (error) {
      // Error already handled by store + API interceptor
      this.loginError = error.message || 'Login failed';
    }
  },

  async handleCredentialResponse(response) {
    this.loginError = '';

    try {
      // Send token to backend for verification
      await this.$store.dispatch('googleLogin', response.credential);

      // If successful, redirect
      this.$router.push('/dashboard');
    } catch (error) {
      this.loginError = error.message || 'Google sign-in failed';
    }
  },
},
```

---

## 📊 SUMMARY TABLE: BEFORE vs AFTER

| Issue | BEFORE (Broken) | AFTER (Fixed) |
|-------|---|---|
| **package.json** | Has composer/php | Only JS packages |
| **API Errors** | No handling | Global interceptors |
| **Token Storage** | `api_token` (never set) | `auth_token` (properly set) |
| **Authentication** | Frontend-only, plaintext | Backend verification, secure |
| **Auth Flow** | Fake, no backend | Real backend API calls |
| **Vuex Store** | No auth actions | Full async login/logout |
| **Route Protection** | Easy to bypass | Verified by backend |
| **Error Messages** | Cryptic/missing | User-friendly, specific |

---

## 🚀 IMPLEMENTATION CHECKLIST

### Step 1: Fix package.json
- [ ] Replace entire file with corrected version
- [ ] Run `npm install`
- [ ] Verify no errors

### Step 2: Create new API service
- [ ] Replace `src/services/api.js` completely
- [ ] Verify file saves correctly
- [ ] Check no syntax errors

### Step 3: Update Vuex store
- [ ] Replace `src/store.js` completely
- [ ] Add `vuex-persist` import
- [ ] Verify file saves

### Step 4: Update router guards
- [ ] Add guard code to `src/router/index.js`
- [ ] Verify routes are protected

### Step 5: Update Login component
- [ ] Update login() method
- [ ] Update handleCredentialResponse() method
- [ ] Test login form

### Step 6: Test everything
- [ ] Run `npm run serve`
- [ ] Test login flow
- [ ] Test API calls
- [ ] Check browser console (should have debug logs)

---

## 📞 TROUBLESHOOTING

### "npm install still fails"
**Solution:** Delete `node_modules` and `package-lock.json`, then run `npm install` again
```bash
rm -r node_modules package-lock.json
npm install
```

### "API calls timeout"
**Solution:** Check if backend is running at `http://localhost:8000`
```bash
# Backend should be running on port 8000
npm run dev  # or your backend start command
```

### "Can't login - 404 error"
**Solution:** Backend API endpoint not found
- Check `.env` file has correct API_URL
- Verify backend has `/api/v1/auth/login` endpoint

### "Login works but token not saved"
**Solution:** Verify api.js response interceptor is working
- Open browser DevTools → Network tab
- Check response has `token` field
- Check Console → should see debug logs

Done! Your auth system is now secure! 🎉

