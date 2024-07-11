<template>
  <div :class="['login-container', { dark: isDarkMode }]">
    <div class="language-switcher">
      <button @click="switchLanguage">{{ nextLanguage }}</button>
    </div>
    <div class="side-panel">
      <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
      <h1>Gittax</h1>
    </div>
    <div class="form-container">
      <form @submit.prevent="login">
        <h2 class="login-title">{{ $t('login.title') }}</h2>
        <p class="no-account">
          {{ $t('login.noAccount') }}
          <router-link to="/signup" class="signup-link">{{ $t('login.signUp') }}</router-link>
        </p>
        <div class="form-group">
          <label for="email">{{ $t('login.email') }}</label>
          <input type="email" v-model="email" required class="styled-input" />
        </div>
        <div class="form-group password-group">
          <label for="password">{{ $t('login.password') }}</label>
          <input type="password" v-model="password" required class="styled-input" ref="passwordField" />
          <img src="@/assets/Gittax/eye.png" alt="Show Password" class="eye-icon" @click="togglePasswordVisibility" />
        </div>
        <button type="submit">{{ $t('login.loginButton') }}</button>
        <router-link to="/forgot-password" class="forgot-password">{{ $t('login.forgotPassword') }}</router-link>
        <button class="google-login">
          <img src="@/assets/Gittax/google.png" alt="Google Logo" class="google-icon" />
          {{ $t('login.googleLogin') }}
        </button>
        <router-link to="/" class="back-home">
          <img src="@/assets/Gittax/back.png" alt="Back Icon" class="back-icon" />
          {{ $t('login.backHome') }}
        </router-link>
      </form>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { i18n } from '@/i18n'; // Adjust this import according to your project structure
import '@/assets/styles/LoginPage.css'; // Import the new CSS file

export default {
  name: 'LoginPage',
  data() {
    return {
      email: '',
      password: '',
      showPassword: false
    };
  },
  computed: {
    ...mapState(['locale', 'isDarkMode']),
    nextLanguage() {
      return this.locale === 'en' ? 'اللغة العربية' : 'English';
    }
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode']),
    login() {
      const users = JSON.parse(localStorage.getItem('users')) || [];
      const user = users.find(user => user.email === this.email && user.password === this.password);
      if (user) {
        localStorage.setItem('loggedInUser', JSON.stringify(user));
        this.$router.push('/notification-settings');
      } else {
        alert('Invalid email or password');
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
      const passwordField = this.$refs.passwordField;
      passwordField.type = this.showPassword ? 'text' : 'password';
    },
     switchLanguage() {
      const newLocale = this.locale === 'en' ? 'ar' : 'en';
      this.setLocale(newLocale);
      i18n.locale = newLocale;
    }
  }
};
</script>
