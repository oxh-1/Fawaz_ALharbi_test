import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    locale: localStorage.getItem('locale') || 'en',
    isDarkMode: JSON.parse(localStorage.getItem('isDarkMode')) || false
  },
  mutations: {
    SET_LOCALE(state, locale) {
      state.locale = locale;
      localStorage.setItem('locale', locale);
    },
    TOGGLE_DARK_MODE(state) {
      state.isDarkMode = !state.isDarkMode;
      localStorage.setItem('isDarkMode', state.isDarkMode);
    },
    SET_DARK_MODE(state, isDarkMode) {
      state.isDarkMode = isDarkMode;
      localStorage.setItem('isDarkMode', isDarkMode);
    }
  },
  actions: {
    setLocale({ commit }, locale) {
      commit('SET_LOCALE', locale);
    },
    toggleDarkMode({ commit }) {
      commit('TOGGLE_DARK_MODE');
    },
    setDarkMode({ commit }, isDarkMode) {
      commit('SET_DARK_MODE', isDarkMode);
    }
  }
});
