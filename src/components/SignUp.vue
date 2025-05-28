<template>
  <div :class="['signup-container', { dark: isDarkMode }]">
    <div class="language-switcher">
      <button @click="switchLanguage">{{ nextLanguage }}</button>
    </div>
    <div class="side-panel">
      <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
      <h1>Gittax</h1>
    </div>
    <div class="form-container">
      <form @submit.prevent="signUp">
        <h2 class="signup-title">{{ $t('signup.title') }}</h2>
        <p class="have-account">
          {{ $t('signup.haveAccount') }}
          <router-link to="/login" class="login-link">{{ $t('signup.login') }}</router-link>
        </p>
        <div class="form-group">
          <h3 for="username">{{ $t('signup.username') }}</h3>
          <input type="text" v-model="username" required class="styled-input" />
        </div>
        <div class="form-group">
          <h3 for="email">{{ $t('signup.email') }}</h3>
          <input type="email" v-model="email" required class="styled-input" />
        </div>
        <div class="form-group">
          <h3 for="password">{{ $t('signup.password') }}</h3>
          <input type="password" v-model="password" required class="styled-input" />
        </div>
        <div class="form-group">
          <h3 for="confirmPassword">{{ $t('signup.confirmPassword') }}</h3>
          <input type="password" v-model="confirmPassword" required class="styled-input" />
        </div>
        <button type="submit">{{ $t('signup.signUpButton') }}</button>
        <router-link to="/" class="back-home">
          <img src="@/assets/Gittax/back.png" alt="Back Icon" class="back-icon" />
          {{ $t('signup.backHome') }}
        </router-link>
      </form>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { i18n } from '@/i18n'; // Adjust this import according to your project structure
import '@/assets/styles/SignupPage.css'; // Import the new CSS file

export default {
  name: 'SignUp',
  data() {
    return {
      username: '',
      email: '',
      password: '',
      confirmPassword: ''
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
    signUp() {
      if (this.password !== this.confirmPassword) {
        alert('Passwords do not match');
        return;
      }
      const users = JSON.parse(localStorage.getItem('users')) || [];
      const userExists = users.some(user => user.email === this.email);
      if (userExists) {
        alert('User already exists');
        return;
      }
      const newUser = {
        username: this.username,
        email: this.email,
        password: this.password
      };
      users.push(newUser);
      localStorage.setItem('users', JSON.stringify(users));
      alert('User registered successfully');
      this.$router.push('/login');
    },
    switchLanguage() {
      const newLocale = this.locale === 'en' ? 'ar' : 'en';
      this.setLocale(newLocale);
      i18n.locale = newLocale;
    }
  }
};
</script>
