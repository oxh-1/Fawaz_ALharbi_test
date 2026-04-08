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
          <router-link to="/signup" class="signup-link">{{ $t('login.signUp') }}</router-link>
        </h4>
        <div class="form-group">
          <h3 for="email">{{ $t('login.email') }}</h3>
          <input type="email" v-model="email" class="styled-input" />
        </div>
        <div class="form-group password-group">
          <h3 for="password">{{ $t('login.password') }}</h3>
          <div class="input-wrapper">
            <input :type="showPassword ? 'text' : 'password'" v-model="password" class="styled-input" ref="passwordField" />
            <img :src="require('@/assets/Gittax/eye.png')"
              alt="Toggle Password"
              class="eye-icon"
              @click="togglePasswordVisibility"/>
          </div>
        </div>

        <div v-if="loginError" class="error-message">{{ loginError }}</div>

        <button type="submit">{{ $t('login.loginButton') }}</button>
        <router-link to="/forgot-password" class="forgot-password">{{ $t('login.forgotPassword') }}</router-link>

        <!-- Google Sign-In button renders here -->
        <div id="google-btn" class="google-btn-container"></div>

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
import i18n from '@/i18n';
import '@/assets/styles/LoginPage.css';
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
      showPopup: false,
      showPassword: false,
      loginError: ''
    };
  },
  computed: {
    ...mapState(['locale', 'isDarkMode']),
    nextLanguage() {
      return this.locale === 'en' ? 'اللغة العربية' : 'English';
    }
  },
  mounted() {
    this.initGoogleLogin();
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode', 'setUser']),

    initGoogleLogin() {
      if (!document.getElementById('google-jssdk')) {
        const script = document.createElement('script');
        script.id = 'google-jssdk';
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        script.onload = () => this.initializeGoogleButton();
      } else {
        // Script already loaded — initialize immediately or wait a tick
        if (window.google) {
          this.initializeGoogleButton();
        } else {
          setTimeout(() => this.initializeGoogleButton(), 500);
        }
      }
    },

    initializeGoogleButton() {
      if (window.google) {
        window.google.accounts.id.initialize({
          client_id: '784439266873-aonrmg0pg5k9tp102dkeliumja0969cq.apps.googleusercontent.com',
          callback: this.handleCredentialResponse,
        });

        window.google.accounts.id.renderButton(
          document.getElementById('google-btn'),
          { theme: 'outline', size: 'large', width: '100%' }
        );
      } else {
        console.error('Google GSI script not loaded yet');
      }
    },

    /**
     * Verifies the Google ID token client-side via Google's tokeninfo endpoint.
     * This avoids the need for a backend server and works in local dev.
     */
    async handleCredentialResponse(response) {
      this.loginError = '';
      try {
        const res = await fetch(
          `https://oauth2.googleapis.com/tokeninfo?id_token=${response.credential}`
        );
        const payload = await res.json();

        if (!payload || payload.error || !payload.sub) {
          this.loginError = 'Google sign-in failed. Please try again.';
          console.error('Token verification failed:', payload.error);
          return;
        }

        const user = {
          id: payload.sub,
          email: payload.email,
          name: payload.name,
          picture: payload.picture
        };

        // Commit to Vuex (which also saves to localStorage)
        this.setUser(user);
        this.$router.push('/notification-settings');

      } catch (error) {
        console.error('Google login error:', error);
        this.loginError = 'A network error occurred. Please check your connection.';
      }
    },

    login() {
      this.loginError = '';
      const users = JSON.parse(localStorage.getItem('users')) || [];
      const user = users.find(u => u.email === this.email && u.password === this.password);
      if (user) {
        this.setUser(user);
        this.$router.push('/notification-settings');
      } else {
        this.loginError = 'Invalid email or password.';
      }
    },

    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },

    switchLanguage() {
      const newLocale = this.locale === 'en' ? 'ar' : 'en';
      this.setLocale(newLocale);
      i18n.locale = newLocale;
    },

    handleAccept() {
      this.showPopup = false;
    },

    handleReject() {
      this.showPopup = false;
    }
  }
};
</script>
