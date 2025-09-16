<template>
  <div :class="['login-container', { dark: isDarkMode }]">
    
    <div class="side-panel">
      <img src="@/assets/Gittax/logo1.png"  class="logo-img"  />
      <h1>Fawaz Platform</h1>
    </div>
    <div class="form-container">
      <form @submit.prevent="login">
        <h2 class="login-title">{{ $t('login.title') }}</h2>
        <h4 class="no-account">
          {{ $t('login.noAccount') }}
          <router-link  to="/signup" class="signup-link">{{ $t('login.signUp') }}</router-link>
        </h4>
        <div class="form-group">
          <h3 for="email">{{ $t('login.email') }}</h3>
          <input type="email" v-model="email"  class="styled-input" />
        </div>
        <div class="form-group password-group">
          <h3 for="password">{{ $t('login.password') }}</h3>
          
<div class="input-wrapper">
          <input   :type="showPassword ? 'text' : 'password'" v-model="password"  class="styled-input" ref="passwordField" />
          <img :src="showPassword ? require('@/assets/Gittax/eye.png') : require('@/assets/Gittax/eye.png')"
    alt="Toggle Password"
    class="eye-icon"
    @click="togglePasswordVisibility"/>
        </div>
</div>







        
        <button type="submit">{{ $t('login.loginButton') }}</button>
        <router-link to="/forgot-password" class="forgot-password">{{ $t('login.forgotPassword') }}</router-link>
        <button class="google-login">
          <img src="@/assets/Gittax/google.png" alt="Google Logo" class="google-icon" />
          {{ $t('login.googleLogin') }}
        </button>
        <router-link  to="/" class="back-home">
          <img src="@/assets/Gittax/back.png" alt="Back Icon" class="back-icon" />
          {{ $t('login.backHome') }}
        </router-link>
    <div class="language-switcher">
      <button type="button" @click="switchLanguage">{{ nextLanguage }}</button>
    </div>
    <PopupNotification
  v-if="showPopup"
  :message="'Do you accept the terms and conditions?'"
  :acceptText="'Accept'"
  :rejectText="'Reject'"
  @accept="handleAccept"
  @reject="handleReject"
/>

      </form>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { i18n } from '@/i18n'; // Adjust this import according to your project structure
import '@/assets/styles/LoginPage.css'; // Import the new CSS file
import PopupNotification from '../components/PopupNotification.vue';



export default {
  name: 'LoginPage',
    components: {
    PopupNotification
  },
  data() {
    return {
      email: '',
      password: '',
      showPopup: true,

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
         this.$router.push('/notification-settings');
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

    
      
      i18n.locale = newLocale;
      this.isArabic = !this.isArabic;
    

    }
  }
};
</script>
