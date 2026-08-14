<template>
  <div :class="['signup-page-wrapper', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Ambient glow -->
    <div class="glow-orb glow-orb-1"></div>
    <div class="glow-orb glow-orb-2"></div>

    <div class="auth-card">
      <!-- Left / Brand Panel -->
      <div class="brand-panel">
        <div class="brand-header">
          <img src="@/assets/Gittax/logo1.png" alt="Platform Logo" class="brand-logo" />
          <h2 class="brand-title">Join Fawaz Platform</h2>
          <span class="brand-tagline">Create your company account and launch your workspace in seconds</span>
        </div>

        <div class="brand-features">
          <div class="feature-item">
            <span class="feature-icon">🚀</span>
            <div>
              <strong>Instant Setup</strong>
              <p>Get immediate access to merchant tools & booking engine</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon">💳</span>
            <div>
              <strong>Integrated Settlements</strong>
              <p>Automated invoicing, settlements, and commission tracking</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon">🌐</span>
            <div>
              <strong>Multi-Lingual & Multi-Role</strong>
              <p>Full Arabic & English support with flexible RBAC</p>
            </div>
          </div>
        </div>

        <div class="trust-badge">
          <span>🛡️ 256-bit SSL Encrypted • ISO Compliant</span>
        </div>
      </div>

      <!-- Right / Form Panel -->
      <div class="form-panel">
        <!-- Top Toolbar -->
        <div class="auth-toolbar">
          <button type="button" class="tool-btn" @click="switchLanguage" :title="$t('notificationSettings.language')">
            🌐 {{ isArabic ? 'English' : 'العربية' }}
          </button>
          <button type="button" class="tool-btn" @click="toggleDarkMode" :title="$t('notificationSettings.nightMode')">
            {{ isDarkMode ? '☀️ Light' : '🌙 Dark' }}
          </button>
        </div>

        <div class="form-header">
          <h1 class="auth-heading">{{ $t('signup.title') }}</h1>
          <p class="auth-subheading">
            {{ $t('signup.haveAccount') }}
            <router-link to="/login" class="highlight-link">{{ $t('signup.login') }}</router-link>
          </p>
        </div>

        <form @submit.prevent="handleSignUp" class="auth-form">
          <!-- Error Alert -->
          <div v-if="errorMessage" class="auth-alert error">
            <span class="alert-icon">⚠️</span>
            <span class="alert-text">{{ errorMessage }}</span>
            <button type="button" class="alert-close" @click="errorMessage = ''">✕</button>
          </div>

          <!-- Success Alert -->
          <div v-if="successMessage" class="auth-alert success">
            <span class="alert-icon">✅</span>
            <span class="alert-text">{{ successMessage }}</span>
          </div>

          <!-- Full Name / Username -->
          <div class="form-group">
            <label for="signup-name">{{ $t('signup.username') }}</label>
            <div class="input-container">
              <span class="input-icon">👤</span>
              <input
                id="signup-name"
                type="text"
                v-model="name"
                required
                class="modern-input"
                placeholder="Fawaz Alharbi"
                autocomplete="name"
              />
            </div>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="signup-email">{{ $t('signup.email') }}</label>
            <div class="input-container">
              <span class="input-icon">✉️</span>
              <input
                id="signup-email"
                type="email"
                v-model="email"
                required
                class="modern-input"
                placeholder="fawaz@example.com"
                autocomplete="email"
              />
            </div>
          </div>

          <!-- Password Grid (2 columns on wide) -->
          <div class="password-grid">
            <div class="form-group">
              <label for="signup-password">{{ $t('signup.password') }}</label>
              <div class="input-container">
                <span class="input-icon">🔑</span>
                <input
                  id="signup-password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="password"
                  required
                  minlength="6"
                  class="modern-input"
                  placeholder="Min 6 characters"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="toggle-pwd-btn"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? '🙈' : '👁️' }}
                </button>
              </div>
            </div>

            <div class="form-group">
              <label for="signup-confirm">{{ $t('signup.confirmPassword') }}</label>
              <div class="input-container">
                <span class="input-icon">🔒</span>
                <input
                  id="signup-confirm"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="passwordConfirmation"
                  required
                  minlength="6"
                  class="modern-input"
                  placeholder="Repeat password"
                  autocomplete="new-password"
                />
              </div>
            </div>
          </div>

          <!-- Terms agreement checkbox -->
          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" v-model="agreeTerms" required />
              <span>I agree to the <a href="#terms" @click.prevent="showTermsModal = true">Terms of Service</a> and <a href="#privacy" @click.prevent="showTermsModal = true">Privacy Policy</a></span>
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="submit-btn" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            <span v-else>{{ $t('signup.signUpButton') }}</span>
          </button>

          <!-- Back to login link -->
          <div class="footer-links">
            <router-link to="/login" class="back-link">
              ← {{ $t('signup.backHome') }}
            </router-link>
          </div>
        </form>

        <!-- Terms Modal -->
        <PopupNotification
          v-if="showTermsModal"
          :message="'By creating an account, you agree to comply with our platform policies, fair use guidelines, and tenant security standards.'"
          :acceptText="'Accept'"
          :rejectText="'Close'"
          @accept="showTermsModal = false; agreeTerms = true"
          @reject="showTermsModal = false"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import PopupNotification from './PopupNotification.vue';

export default {
  name: 'SignUp',
  components: {
    PopupNotification
  },
  data() {
    return {
      name: '',
      email: '',
      password: '',
      passwordConfirmation: '',
      agreeTerms: true,
      showPassword: false,
      showTermsModal: false,
      errorMessage: '',
      successMessage: '',
      isLoading: false
    };
  },
  computed: {
    ...mapState({
      locale:     state => state.settings ? state.settings.locale : 'en',
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
    }),
    isArabic() {
      return this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    }
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode']),

    async handleSignUp() {
      this.errorMessage = '';
      this.successMessage = '';

      if (this.password !== this.passwordConfirmation) {
        this.errorMessage = 'Passwords do not match. Please verify and try again.';
        return;
      }

      if (this.password.length < 6) {
        this.errorMessage = 'Password must be at least 6 characters long.';
        return;
      }

      if (!this.agreeTerms) {
        this.errorMessage = 'Please accept the Terms and Conditions to proceed.';
        return;
      }

      this.isLoading = true;
      try {
        await this.$store.dispatch('register', {
          name:                  this.name,
          email:                 this.email,
          password:              this.password,
          password_confirmation: this.passwordConfirmation,
        });

        this.successMessage = 'Account created successfully! Redirecting to dashboard...';
        setTimeout(() => {
          this.$router.push('/dashboard');
        }, 1000);
      } catch (error) {
        this.errorMessage = error.message || 'Registration failed. Please check your information or try again.';
      } finally {
        this.isLoading = false;
      }
    },

    switchLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.setLocale(newLocale);
      if (this.$i18n) {
        this.$i18n.locale = newLocale;
      }
    }
  }
};
</script>

<style scoped>
.signup-page-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
  padding: 24px;
  position: relative;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  color: #1e293b;
  box-sizing: border-box;
}

.signup-page-wrapper.dark {
  background: linear-gradient(135deg, #0f172a 0%, #1e1e2e 100%);
  color: #f8fafc;
}

.signup-page-wrapper.rtl {
  direction: rtl;
}

/* Ambient glow */
.glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  opacity: 0.6;
}
.glow-orb-1 {
  width: 400px;
  height: 400px;
  top: -100px;
  left: -100px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, rgba(16, 185, 129, 0) 70%);
}
.glow-orb-2 {
  width: 350px;
  height: 350px;
  bottom: -80px;
  right: -80px;
  background: radial-gradient(circle, rgba(0, 170, 255, 0.35) 0%, rgba(0, 170, 255, 0) 70%);
}

/* Auth Card */
.auth-card {
  display: flex;
  width: 100%;
  max-width: 1000px;
  min-height: 600px;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1), 0 0 1px 1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  position: relative;
  z-index: 10;
  transition: all 0.3s ease;
}

.dark .auth-card {
  background: #181824;
  box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.6), 0 0 1px 1px rgba(255, 255, 255, 0.08);
}

/* Brand Panel */
.brand-panel {
  flex: 1;
  background: linear-gradient(145deg, #0284c7 0%, #0369a1 50%, #075985 100%);
  color: #ffffff;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.dark .brand-panel {
  background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
  border-right: 1px solid #2d3748;
}

.brand-header {
  text-align: left;
}
.rtl .brand-header {
  text-align: right;
}

.brand-logo {
  height: 48px;
  width: auto;
  margin-bottom: 16px;
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
}

.brand-title {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0 0 8px 0;
  letter-spacing: -0.5px;
}

.brand-tagline {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.4;
  display: block;
}

.brand-features {
  margin: 32px 0;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.feature-icon {
  font-size: 1.4rem;
  background: rgba(255, 255, 255, 0.15);
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.feature-item strong {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 2px;
}

.feature-item p {
  margin: 0;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.3;
}

.trust-badge {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.75);
  background: rgba(0, 0, 0, 0.15);
  padding: 8px 12px;
  border-radius: 8px;
  text-align: center;
}

/* Form Panel */
.form-panel {
  flex: 1.15;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
}

.auth-toolbar {
  position: absolute;
  top: 20px;
  right: 24px;
  display: flex;
  gap: 8px;
}
.rtl .auth-toolbar {
  right: auto;
  left: 24px;
}

.tool-btn {
  background: rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 20px;
  padding: 6px 12px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  color: inherit;
  transition: all 0.2s ease;
}
.dark .tool-btn {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.12);
}
.tool-btn:hover {
  background: rgba(0, 170, 255, 0.12);
  color: #00aaff;
}

.form-header {
  margin-bottom: 20px;
}

.auth-heading {
  font-size: 1.65rem;
  font-weight: 700;
  margin: 0 0 6px 0;
}

.auth-subheading {
  margin: 0;
  font-size: 0.875rem;
  color: #64748b;
}
.dark .auth-subheading {
  color: #94a3b8;
}

.highlight-link {
  color: #0284c7;
  font-weight: 600;
  text-decoration: none;
  margin-left: 4px;
}
.dark .highlight-link {
  color: #38bdf8;
}
.highlight-link:hover {
  text-decoration: underline;
}

/* Alert */
.auth-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 16px;
  font-size: 0.85rem;
}
.auth-alert.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
.dark .auth-alert.error {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.4);
}
.auth-alert.success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}
.dark .auth-alert.success {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
  border-color: rgba(34, 197, 94, 0.4);
}
.alert-text {
  flex: 1;
}
.alert-close {
  background: none;
  border: none;
  color: inherit;
  font-weight: bold;
  cursor: pointer;
}

/* Form inputs */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.password-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 500px) {
  .password-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}
.rtl .form-group {
  text-align: right;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
}
.dark .form-group label {
  color: #cbd5e1;
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  font-size: 1rem;
  pointer-events: none;
  opacity: 0.6;
}
.rtl .input-icon {
  left: auto;
  right: 12px;
}

.modern-input {
  width: 100%;
  padding: 11px 14px 11px 38px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  color: #0f172a;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.rtl .modern-input {
  padding: 11px 38px 11px 14px;
}

.dark .modern-input {
  background: #1e1e2e;
  border-color: #334155;
  color: #f1f5f9;
}

.modern-input:focus {
  border-color: #0284c7;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
.dark .modern-input:focus {
  border-color: #38bdf8;
  background: #181824;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
}

.toggle-pwd-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 4px;
}
.rtl .toggle-pwd-btn {
  right: auto;
  left: 10px;
}

.form-options {
  font-size: 0.8rem;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  cursor: pointer;
  color: #64748b;
  line-height: 1.3;
}
.dark .checkbox-label {
  color: #94a3b8;
}
.checkbox-label a {
  color: #0284c7;
  text-decoration: underline;
}
.dark .checkbox-label a {
  color: #38bdf8;
}

.submit-btn {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
  margin-top: 4px;
}
.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
}
.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.footer-links {
  text-align: center;
  margin-top: 4px;
}

.back-link {
  font-size: 0.85rem;
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}
.dark .back-link {
  color: #94a3b8;
}
.back-link:hover {
  color: #0284c7;
  text-decoration: underline;
}

/* Spinner */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 850px) {
  .auth-card {
    flex-direction: column;
    max-width: 480px;
  }
  .brand-panel {
    padding: 24px;
  }
  .brand-features {
    display: none;
  }
  .form-panel {
    padding: 24px;
  }
}
</style>
