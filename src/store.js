import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import { authApi } from './services/api';

Vue.use(Vuex);

// ─────────────────────────────────────────────────────────────────────────────
// PERSISTENCE — save auth + settings slices to localStorage automatically
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
    // ─── Auth ───
    auth: {
      token:     localStorage.getItem('auth_token') || null,
      user:      JSON.parse(localStorage.getItem('loggedInUser')) || null,
      isLoggedIn: !!localStorage.getItem('auth_token'),
      isLoading: false,
      error:     null,
    },

    // ─── UI ───
    ui: {
      toasts:    [],
      isLoading: false,
    },

    // ─── Settings ───
    settings: {
      locale:    localStorage.getItem('locale') || 'en',
      isDarkMode: JSON.parse(localStorage.getItem('isDarkMode')) || false,
    },
  },

  // ─────────────────────────────────────────────────────────────────────────
  // MUTATIONS
  // ─────────────────────────────────────────────────────────────────────────
  mutations: {
    // Auth
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
      state.auth.token     = null;
      state.auth.user      = null;
      state.auth.isLoggedIn = false;
      state.auth.error     = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('loggedInUser');
    },

    // Settings
    SET_LOCALE(state, locale) {
      state.settings.locale = locale;
      localStorage.setItem('locale', locale);
    },
    TOGGLE_DARK_MODE(state) {
      state.settings.isDarkMode = !state.settings.isDarkMode;
      localStorage.setItem('isDarkMode', state.settings.isDarkMode);
    },
    SET_DARK_MODE(state, isDarkMode) {
      state.settings.isDarkMode = isDarkMode;
      localStorage.setItem('isDarkMode', isDarkMode);
    },

    // UI / Toasts
    ADD_TOAST(state, toast) {
      state.ui.toasts.push({
        id:       Date.now(),
        type:     'info',
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
  // ACTIONS
  // ─────────────────────────────────────────────────────────────────────────
  actions: {
    // ─── Login ───
    async login({ commit }, credentials) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);
      try {
        const response = await authApi.login(credentials.email, credentials.password);
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);
        commit('ADD_TOAST', { type: 'success', message: `Welcome back, ${response.user.name}!` });
        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', { type: 'error', message: error.message, autoClose: false });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── Register ───
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
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);
        commit('ADD_TOAST', { type: 'success', message: 'Account created successfully! Welcome!' });
        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', { type: 'error', message: error.message, autoClose: false });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── Google Login ───
    async googleLogin({ commit }, idToken) {
      commit('SET_AUTH_LOADING', true);
      commit('SET_AUTH_ERROR', null);
      try {
        const response = await authApi.googleLogin(idToken);
        commit('SET_AUTH_TOKEN', response.token);
        commit('SET_AUTH_USER', response.user);
        commit('ADD_TOAST', { type: 'success', message: `Welcome, ${response.user.name}!` });
        return response;
      } catch (error) {
        commit('SET_AUTH_ERROR', error.message);
        commit('ADD_TOAST', { type: 'error', message: 'Google sign-in failed: ' + error.message });
        throw error;
      } finally {
        commit('SET_AUTH_LOADING', false);
      }
    },

    // ─── Fetch current user (verify token on app boot) ───
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

    // ─── Logout ───
    async logout({ commit }) {
      try {
        await authApi.logout();
      } catch (error) {
        // Continue with local logout even if backend call fails
        console.error('Logout API error:', error);
      } finally {
        commit('LOGOUT');
        commit('ADD_TOAST', { type: 'info', message: 'You have been logged out.' });
      }
    },

    // ─── Legacy helpers (keep backward-compat with components using setUser) ───
    setUser({ commit }, user) {
      commit('SET_AUTH_USER', user);
    },

    // ─── Settings ───
    setLocale({ commit }, locale) {
      commit('SET_LOCALE', locale);
    },
    toggleDarkMode({ commit }) {
      commit('TOGGLE_DARK_MODE');
    },
    setDarkMode({ commit }, isDarkMode) {
      commit('SET_DARK_MODE', isDarkMode);
    },

    // ─── UI ───
    showToast({ commit }, toast) {
      const toastId = Date.now();
      commit('ADD_TOAST', { ...toast, id: toastId });
      if (toast.autoClose !== false) {
        setTimeout(() => commit('REMOVE_TOAST', toastId), toast.duration || 3000);
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
  // GETTERS
  // ─────────────────────────────────────────────────────────────────────────
  getters: {
    isAuthenticated: state => state.auth.isLoggedIn,
    currentUser:     state => state.auth.user,
    authToken:       state => state.auth.token,
    isAuthLoading:   state => state.auth.isLoading,
    authError:       state => state.auth.error,
    // settings — keep same names as existing components use
    locale:          state => state.settings.locale,
    isDarkMode:      state => state.settings.isDarkMode,
    // ui
    toasts:          state => state.ui.toasts,
    isLoading:       state => state.ui.isLoading,
  },

  plugins: [vuexLocal.plugin],
});
