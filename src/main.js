import Vue from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import i18n from './i18n';
import '@/assets/styles/global.css';

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app');

// Update i18n locale based on Vuex state
i18n.locale = store.state.locale;

store.watch(
  (state) => state.locale,
  (locale) => {
    i18n.locale = locale;
  }
);


// Apply dark mode based on Vuex state
document.body.classList.toggle('dark-mode', store.state.isDarkMode);

// Watch for changes in Vuex state and apply dark mode
store.watch(
  (state) => state.isDarkMode,
  (isDarkMode) => {
    document.body.classList.toggle('dark-mode', isDarkMode);
  }
);
