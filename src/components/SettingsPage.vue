<template>
  <div :class="['platform-settings-root', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="settings-navbar">
      <div class="nav-left">
        <router-link to="/dashboard" class="brand-link">
          <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
          <span class="brand-name">Fawaz Platform</span>
        </router-link>
        <span class="nav-divider">/</span>
        <span class="nav-page-badge">⚙️ Platform Settings</span>
      </div>

      <div class="nav-right">
        <router-link to="/dashboard" class="nav-pill-btn">🏠 Hub</router-link>
        <router-link to="/profile" class="nav-pill-btn">👤 Profile</router-link>
        <router-link to="/c2/home" class="nav-pill-btn">🏢 Company 2</router-link>
        <router-link to="/c3/stocks" class="nav-pill-btn">📈 Company 3</router-link>
        <router-link to="/c4/properties" class="nav-pill-btn">🏢 Company 4</router-link>
        <router-link to="/c5/academy" class="nav-pill-btn">🎓 Company 5</router-link>

        <button class="nav-icon-btn" @click="toggleDarkMode" :title="'Toggle Dark Mode'">
          {{ isDarkMode ? '☀️' : '🌙' }}
        </button>
        <button class="nav-icon-btn" @click="toggleLanguage" :title="'Switch Language'">
          🌐 {{ isArabic ? 'EN' : 'عربي' }}
        </button>

        <div class="nav-user-pill">
          <img :src="userAvatar || defaultAvatar" alt="Avatar" class="user-avatar-sm" />
          <span class="user-name-sm">{{ displayName }}</span>
        </div>

        <button class="nav-logout-btn" @click="handleLogout" title="Logout from platform">
          🚪 Logout
        </button>
      </div>
    </header>

    <!-- Main Settings Hub Body -->
    <main class="settings-body-container">
      <!-- Section Header -->
      <div class="settings-header-banner">
        <div>
          <h1 class="settings-main-title">⚙️ Enterprise Platform Settings</h1>
          <p class="settings-subtitle">
            Configure platform branding, regional localization, security rules, notification channels, API keys, and database maintenance.
          </p>
        </div>
        <div class="header-action-group">
          <button class="save-all-btn" @click="saveAllSettings" :disabled="isSaving">
            <span v-if="isSaving" class="btn-spinner"></span>
            <span v-else>💾 Save Changes</span>
          </button>
        </div>
      </div>

      <!-- Settings Layout (Sidebar Navigation Tabs + Content Card) -->
      <div class="settings-layout-grid">
        <!-- Settings Category Sidebar Tabs -->
        <aside class="settings-nav-sidebar">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            :class="['settings-tab-btn', { active: activeTab === tab.id }]"
            @click="activeTab = tab.id"
          >
            <span class="tab-icon">{{ tab.icon }}</span>
            <div class="tab-text-wrap">
              <span class="tab-title">{{ tab.label }}</span>
              <span class="tab-desc">{{ tab.desc }}</span>
            </div>
            <span v-if="activeTab === tab.id" class="active-indicator"></span>
          </button>
        </aside>

        <!-- Active Settings Content Card -->
        <div class="settings-content-card">
          <!-- Banner Alert -->
          <div v-if="saveSuccess" class="success-banner">
            ✅ Settings updated successfully! All changes have been applied.
          </div>

          <!-- TAB 1: Platform & Branding -->
          <section v-if="activeTab === 'general'" class="settings-pane">
            <h2 class="pane-title">🏢 Platform Profile & Company Branding</h2>
            <p class="pane-desc">General operational details, brand identification, and tax registration credentials.</p>

            <div class="form-grid-2">
              <div class="form-field">
                <label class="field-label">Platform Name *</label>
                <input v-model="settings.platformName" class="form-input" placeholder="e.g. Fawaz Platform" />
              </div>
              <div class="form-field">
                <label class="field-label">Official Support Email *</label>
                <input v-model="settings.supportEmail" type="email" class="form-input" placeholder="support@fawazplatform.sa" />
              </div>
              <div class="form-field">
                <label class="field-label">Support Phone Number</label>
                <input v-model="settings.supportPhone" class="form-input" placeholder="+966 50 000 0000" />
              </div>
              <div class="form-field">
                <label class="field-label">Commercial Registration (CR)</label>
                <input v-model="settings.crNumber" class="form-input" placeholder="1010789456" />
              </div>
              <div class="form-field">
                <label class="field-label">VAT / Tax ID Number</label>
                <input v-model="settings.taxId" class="form-input" placeholder="300123456700003" />
              </div>
              <div class="form-field">
                <label class="field-label">Primary Currency</label>
                <select v-model="settings.currency" class="form-select">
                  <option value="SAR">SAR — Saudi Riyal (ر.س)</option>
                  <option value="USD">USD — US Dollar ($)</option>
                  <option value="EUR">EUR — Euro (€)</option>
                  <option value="AED">AED — UAE Dirham (د.إ)</option>
                </select>
              </div>
              <div class="form-field full-width">
                <label class="field-label">Headquarters Address</label>
                <textarea v-model="settings.address" rows="2" class="form-input" placeholder="King Fahd Road, Riyadh, Saudi Arabia"></textarea>
              </div>
            </div>
          </section>

          <!-- TAB 2: Appearance & Experience -->
          <section v-if="activeTab === 'appearance'" class="settings-pane">
            <h2 class="pane-title">🎨 Appearance, Theme & Interface Customization</h2>
            <p class="pane-desc">Customize UI color themes, night mode, density, and sound effects.</p>

            <div class="settings-rows-list">
              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Dark Mode Theme</span>
                  <span class="setting-item-desc">Enable sleek night-mode dark styling across the platform</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Primary Accent Color</span>
                  <span class="setting-item-desc">Choose highlight branding accent for buttons, badges, and charts</span>
                </div>
                <div class="color-picker-group">
                  <input type="color" v-model="settings.accentColor" class="color-picker-input" />
                  <span class="color-code">{{ settings.accentColor }}</span>
                </div>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Sound Effects & AI Audio Feedback</span>
                  <span class="setting-item-desc">Play chime upon actions, toast alerts, and AI voice replies</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.soundEffects" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Compact Table Density</span>
                  <span class="setting-item-desc">Display denser tables to view more records per screen</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.compactDensity" />
                  <span class="slider-round"></span>
                </label>
              </div>
            </div>
          </section>

          <!-- TAB 3: Language & Localization -->
          <section v-if="activeTab === 'localization'" class="settings-pane">
            <h2 class="pane-title">🌐 Language, Regional & Localization Settings</h2>
            <p class="pane-desc">Configure language preferences, timezone, date formatting, and right-to-left layout.</p>

            <div class="settings-rows-list">
              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Interface Display Language</span>
                  <span class="setting-item-desc">Switch between English and Arabic with automatic layout mirroring</span>
                </div>
                <div class="lang-selector-group">
                  <button
                    :class="['lang-option-btn', { active: !isArabic }]"
                    @click="setLanguage('en')">
                    🇬🇧 English (LTR)
                  </button>
                  <button
                    :class="['lang-option-btn', { active: isArabic }]"
                    @click="setLanguage('ar')">
                    🇸🇦 العربية (RTL)
                  </button>
                </div>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Timezone</span>
                  <span class="setting-item-desc">Platform server and appointment scheduling timezone</span>
                </div>
                <select v-model="settings.timezone" class="form-select inline-select">
                  <option value="Asia/Riyadh">Asia/Riyadh (GMT+3:00 - KSA)</option>
                  <option value="Asia/Dubai">Asia/Dubai (GMT+4:00 - UAE)</option>
                  <option value="UTC">UTC (Universal Time)</option>
                </select>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Date Format</span>
                  <span class="setting-item-desc">Format used for invoices, booking reports, and logs</span>
                </div>
                <select v-model="settings.dateFormat" class="form-select inline-select">
                  <option value="YYYY-MM-DD">YYYY-MM-DD (2026-08-14)</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY (14/08/2026)</option>
                  <option value="MM/DD/YYYY">MM/DD/YYYY (08/14/2026)</option>
                </select>
              </div>
            </div>
          </section>

          <!-- TAB 4: Notifications -->
          <section v-if="activeTab === 'notifications'" class="settings-pane">
            <h2 class="pane-title">🔔 Notification Preferences & Broadcasts</h2>
            <p class="pane-desc">Manage system alert triggers, booking emails, settlement SMS alerts, and marketing digests.</p>

            <div class="settings-rows-list">
              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">📧 Email Booking Confirmations</span>
                  <span class="setting-item-desc">Send immediate email confirmation when customers book appointments</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.notifEmailBooking" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">📱 SMS Settlement Alerts</span>
                  <span class="setting-item-desc">Receive real-time SMS messages when merchant payouts are approved</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.notifSmsSettlement" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">🤖 AI Assistant Automated Briefings</span>
                  <span class="setting-item-desc">Receive daily morning performance summary from Fawaz AI</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.notifAiBriefing" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">⚠️ Security & Unrecognized Login Alerts</span>
                  <span class="setting-item-desc">Notify Super Admin when account logs in from a new IP address or device</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.notifSecurityAlerts" />
                  <span class="slider-round"></span>
                </label>
              </div>
            </div>
          </section>

          <!-- TAB 5: Security & Active Sessions -->
          <section v-if="activeTab === 'security'" class="settings-pane">
            <h2 class="pane-title">🔒 Security, 2FA & Active Sessions</h2>
            <p class="pane-desc">Manage account access controls, two-factor authentication, and active logged-in devices.</p>

            <div class="settings-rows-list">
              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Two-Factor Authentication (2FA)</span>
                  <span class="setting-item-desc">Require one-time verification code via Authenticator App</span>
                </div>
                <label class="switch-toggle">
                  <input type="checkbox" v-model="settings.twoFactorAuth" />
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-item-row">
                <div class="setting-item-meta">
                  <span class="setting-item-name">Auto Session Inactivity Timeout</span>
                  <span class="setting-item-desc">Automatically lock interface after period of inactivity</span>
                </div>
                <select v-model="settings.sessionTimeout" class="form-select inline-select">
                  <option value="15">15 Minutes</option>
                  <option value="30">30 Minutes</option>
                  <option value="60">1 Hour</option>
                  <option value="0">Never (Stay Logged In)</option>
                </select>
              </div>
            </div>

            <!-- Active Sessions List -->
            <div class="security-box">
              <h3 class="security-box-title">💻 Current Active Session</h3>
              <div class="session-card">
                <div class="session-icon">🖥️</div>
                <div class="session-info">
                  <span class="session-name">Windows PC — Chrome Web Browser</span>
                  <span class="session-meta">IP: 127.0.0.1 • Location: Riyadh, Saudi Arabia • 🟢 Active Now</span>
                </div>
                <span class="current-badge">This Device</span>
              </div>
            </div>
          </section>

          <!-- TAB 6: Developer API & Webhooks -->
          <section v-if="activeTab === 'api'" class="settings-pane">
            <h2 class="pane-title">⚡ Developer REST API & Webhooks</h2>
            <p class="pane-desc">Integration credentials, API endpoints, webhook subscriptions, and Sanctum access tokens.</p>

            <div class="api-key-box">
              <div class="api-key-header">
                <div>
                  <span class="api-key-label">Production API Secret Key</span>
                  <p class="api-key-sub">Used to authorize external server integrations and mobile apps.</p>
                </div>
                <button class="regen-btn" @click="generateNewApiKey">🔄 Rotate Key</button>
              </div>
              <div class="api-key-input-wrap">
                <input :type="showKey ? 'text' : 'password'" :value="settings.apiKey" readonly class="api-key-input" />
                <button class="copy-key-btn" @click="showKey = !showKey">
                  {{ showKey ? '🙈 Hide' : '👁️ View' }}
                </button>
                <button class="copy-key-btn primary" @click="copyApiKey">
                  📋 {{ copied ? 'Copied!' : 'Copy' }}
                </button>
              </div>
            </div>

            <div class="form-field full-width" style="margin-top:20px">
              <label class="field-label">Webhook Callback URL</label>
              <input v-model="settings.webhookUrl" class="form-input" placeholder="https://your-server.com/webhooks/fawaz-events" />
              <span class="field-hint">We send POST payloads on `booking.created`, `settlement.completed`, `customer.registered`.</span>
            </div>
          </section>

          <!-- TAB 7: Danger Zone & Session Logout -->
          <section v-if="activeTab === 'danger'" class="settings-pane">
            <h2 class="pane-title text-danger">⚠️ Danger Zone & Account Sessions</h2>
            <p class="pane-desc">Perform critical account actions, session revocation, and platform logout.</p>

            <div class="danger-box">
              <div class="danger-action-row">
                <div>
                  <span class="danger-row-title">Sign Out of This Device</span>
                  <p class="danger-row-desc">Safely terminate your current session and return to the login screen.</p>
                </div>
                <button class="danger-btn" @click="handleLogout">
                  🚪 Logout Now
                </button>
              </div>

              <div class="danger-divider"></div>

              <div class="danger-action-row">
                <div>
                  <span class="danger-row-title">Revoke All Active Sessions</span>
                  <p class="danger-row-desc">Log out of all devices and mobile sessions everywhere.</p>
                </div>
                <button class="danger-btn outline" @click="handleRevokeAll">
                  🔒 Revoke All Sessions
                </button>
              </div>
            </div>
          </section>

        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'SettingsPage',
  data() {
    return {
      activeTab: 'general',
      isSaving: false,
      saveSuccess: false,
      showKey: false,
      copied: false,
      displayName: 'Fawaz Alharbi',
      userEmail: 'fawazalharbi04@gmail.com',
      userAvatar: '',
      isArabic: false,
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      tabs: [
        { id: 'general',       icon: '🏢', label: 'Company & Platform', desc: 'Brand name, CR & tax IDs' },
        { id: 'appearance',    icon: '🎨', label: 'Appearance & UI',    desc: 'Theme, dark mode & colors' },
        { id: 'localization',  icon: '🌐', label: 'Language & Locale',  desc: 'Arabic/English, timezone' },
        { id: 'notifications', icon: '🔔', label: 'Notifications',      desc: 'Email, SMS, AI briefing' },
        { id: 'security',      icon: '🔒', label: 'Security & 2FA',     desc: 'Sessions, auth & timeout' },
        { id: 'api',           icon: '⚡', label: 'API & Webhooks',     desc: 'Sanctum tokens & webhooks' },
        { id: 'danger',        icon: '⚠️', label: 'Danger Zone',        desc: 'Session logout & actions' },
      ],
      settings: {
        platformName: 'Fawaz Platform Enterprise',
        supportEmail: 'fawazalharbi04@gmail.com',
        supportPhone: '+966 50 123 4567',
        crNumber: '1010892341',
        taxId: '300987654300003',
        currency: 'SAR',
        address: 'King Fahd Road, Riyadh, Saudi Arabia',
        accentColor: '#0284c7',
        soundEffects: true,
        compactDensity: false,
        timezone: 'Asia/Riyadh',
        dateFormat: 'YYYY-MM-DD',
        notifEmailBooking: true,
        notifSmsSettlement: true,
        notifAiBriefing: true,
        notifSecurityAlerts: true,
        twoFactorAuth: false,
        sessionTimeout: '30',
        apiKey: 'fwz_live_894fbc90a827419e912bc09123847',
        webhookUrl: 'https://api.fawazplatform.sa/webhooks/v1'
      }
    };
  },
  computed: {
    ...mapState({
      user:       state => state.auth ? state.auth.user : null,
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
      locale:     state => state.settings ? state.settings.locale : 'en',
    }),
    currentUser() {
      return this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    }
  },
  mounted() {
    this.isArabic = this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
    document.body.classList.toggle('rtl', this.isArabic);

    if (this.currentUser) {
      this.displayName = this.currentUser.name || 'Fawaz Alharbi';
      this.userEmail = this.currentUser.email || 'fawazalharbi04@gmail.com';
      this.userAvatar = this.currentUser.picture || this.currentUser.avatar || '';
    }

    // Load custom stored settings if available
    const saved = localStorage.getItem('fawaz_platform_settings');
    if (saved) {
      try {
        this.settings = { ...this.settings, ...JSON.parse(saved) };
      } catch (e) {
        console.warn('Failed to parse settings', e);
      }
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),

    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.setLanguage(newLocale);
    },

    setLanguage(locale) {
      this.isArabic = locale === 'ar';
      this.$i18n.locale = locale;
      this.setLocale(locale);
      document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
      document.body.classList.toggle('rtl', this.isArabic);
    },

    async saveAllSettings() {
      this.isSaving = true;
      this.saveSuccess = false;

      // Persist to local storage & mock save
      localStorage.setItem('fawaz_platform_settings', JSON.stringify(this.settings));

      setTimeout(() => {
        this.isSaving = false;
        this.saveSuccess = true;
        setTimeout(() => {
          this.saveSuccess = false;
        }, 3000);
      }, 500);
    },

    generateNewApiKey() {
      const rand = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
      this.settings.apiKey = `fwz_live_${rand}`;
    },

    copyApiKey() {
      navigator.clipboard.writeText(this.settings.apiKey);
      this.copied = true;
      setTimeout(() => { this.copied = false; }, 2000);
    },

    async handleLogout() {
      try {
        await this.logout();
      } catch (e) {
        console.warn('Logout action handled', e);
      } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('loggedInUser');
        this.$router.push('/login').catch(() => {
          window.location.href = '/login';
        });
      }
    },

    async handleRevokeAll() {
      alert('All other active sessions have been revoked.');
      await this.handleLogout();
    }
  }
};
</script>

<style scoped>
.platform-settings-root {
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.platform-settings-root.dark {
  background: #0f172a;
  color: #f8fafc;
}
.platform-settings-root.rtl {
  direction: rtl;
}

/* Navbar */
.settings-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.dark .settings-navbar {
  background: #181824;
  border-bottom-color: #2d3748;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.brand-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
}
.brand-logo {
  height: 32px;
  width: auto;
  object-fit: contain;
}
.brand-name {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0284c7;
}
.nav-divider {
  color: #94a3b8;
}
.nav-page-badge {
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
}
.dark .nav-page-badge {
  color: #94a3b8;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.nav-pill-btn {
  padding: 6px 12px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 700;
  transition: all 0.2s;
}
.dark .nav-pill-btn {
  background: #282a36;
  color: #e2e8f0;
}
.nav-pill-btn:hover {
  background: #0284c7;
  color: #ffffff;
}

.nav-icon-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 50%;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}
.dark .nav-icon-btn {
  background: #282a36;
  border-color: #475569;
  color: #ffffff;
}

.nav-user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: 30px;
  background: #f1f5f9;
}
.dark .nav-user-pill {
  background: #282a36;
}
.user-avatar-sm {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  object-fit: cover;
}
.user-name-sm {
  font-size: 0.8rem;
  font-weight: 700;
}

.nav-logout-btn {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.nav-logout-btn:hover {
  background: #ef4444;
  color: #ffffff;
}

/* Main Container */
.settings-body-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px 60px;
}

.settings-header-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.settings-main-title {
  font-size: 1.75rem;
  font-weight: 900;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}
.settings-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
}
.dark .settings-subtitle {
  color: #94a3b8;
}

.save-all-btn {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  padding: 10px 24px;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
  transition: all 0.2s;
}
.save-all-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(2, 132, 199, 0.4);
}

/* Settings Layout Grid */
.settings-layout-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
  align-items: flex-start;
}

@media (max-width: 900px) {
  .settings-layout-grid {
    grid-template-columns: 1fr;
  }
}

/* Nav Sidebar */
.settings-nav-sidebar {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.dark .settings-nav-sidebar {
  background: #181824;
  border-color: #2d3748;
}

.settings-tab-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  color: inherit;
  position: relative;
  transition: all 0.2s;
}
.rtl .settings-tab-btn {
  text-align: right;
}
.settings-tab-btn:hover {
  background: #f1f5f9;
}
.dark .settings-tab-btn:hover {
  background: #282a36;
}
.settings-tab-btn.active {
  background: rgba(2, 132, 199, 0.1);
  color: #0284c7;
}

.tab-icon {
  font-size: 1.25rem;
}
.tab-text-wrap {
  display: flex;
  flex-direction: column;
}
.tab-title {
  font-size: 0.875rem;
  font-weight: 700;
}
.tab-desc {
  font-size: 0.72rem;
  color: #64748b;
}
.dark .tab-desc {
  color: #94a3b8;
}

.active-indicator {
  position: absolute;
  right: 12px;
  width: 6px;
  height: 6px;
  background: #0284c7;
  border-radius: 50%;
}
.rtl .active-indicator {
  right: auto;
  left: 12px;
}

/* Content Card */
.settings-content-card {
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  padding: 30px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.dark .settings-content-card {
  background: #181824;
  border-color: #2d3748;
}

.success-banner {
  background: #dcfce7;
  color: #166534;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 0.875rem;
  font-weight: 700;
}

.pane-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0 0 6px 0;
}
.pane-title.text-danger {
  color: #ef4444;
}
.pane-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 24px 0;
}
.dark .pane-desc {
  color: #94a3b8;
}

/* Form Controls */
.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}
@media (max-width: 700px) {
  .form-grid-2 {
    grid-template-columns: 1fr;
  }
}
.full-width {
  grid-column: 1 / -1;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
}
.dark .field-label {
  color: #cbd5e1;
}

.form-input, .form-select {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
}
.dark .form-input, .dark .form-select {
  background: #1e1e2e;
  border-color: #475569;
}
.form-input:focus, .form-select:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.field-hint {
  font-size: 0.72rem;
  color: #94a3b8;
}

/* Settings Rows List */
.settings-rows-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.setting-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 18px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}
.dark .setting-item-row {
  background: #1e1e2e;
  border-color: #2d3748;
}

.setting-item-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.setting-item-name {
  font-size: 0.9rem;
  font-weight: 700;
}
.setting-item-desc {
  font-size: 0.75rem;
  color: #64748b;
}
.dark .setting-item-desc {
  color: #94a3b8;
}

/* Switch */
.switch-toggle {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}
.switch-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider-round {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .3s;
  border-radius: 24px;
}
.slider-round:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
input:checked + .slider-round {
  background-color: #0284c7;
}
input:checked + .slider-round:before {
  transform: translateX(20px);
}

/* Color Picker */
.color-picker-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.color-picker-input {
  width: 40px;
  height: 36px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  background: none;
}
.color-code {
  font-size: 0.85rem;
  font-family: monospace;
  font-weight: 700;
}

/* Lang buttons */
.lang-selector-group {
  display: flex;
  gap: 8px;
}
.lang-option-btn {
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.dark .lang-option-btn {
  background: #282a36;
  border-color: #475569;
}
.lang-option-btn.active {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
}

.inline-select {
  width: auto;
  min-width: 180px;
}

/* Security & Sessions */
.security-box {
  margin-top: 24px;
  padding: 20px;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}
.dark .security-box {
  background: #1e1e2e;
  border-color: #2d3748;
}
.security-box-title {
  font-size: 0.95rem;
  font-weight: 800;
  margin: 0 0 12px 0;
}
.session-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #ffffff;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.dark .session-card {
  background: #282a36;
  border-color: #334155;
}
.session-icon {
  font-size: 1.5rem;
}
.session-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}
.session-name {
  font-size: 0.875rem;
  font-weight: 700;
}
.session-meta {
  font-size: 0.72rem;
  color: #64748b;
}
.dark .session-meta {
  color: #94a3b8;
}
.current-badge {
  background: #dcfce7;
  color: #166534;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 12px;
}

/* API Key Box */
.api-key-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
}
.dark .api-key-box {
  background: #1e1e2e;
  border-color: #2d3748;
}
.api-key-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.api-key-label {
  font-size: 0.9rem;
  font-weight: 800;
}
.api-key-sub {
  font-size: 0.75rem;
  color: #64748b;
  margin: 2px 0 0 0;
}
.dark .api-key-sub {
  color: #94a3b8;
}
.regen-btn {
  background: none;
  border: 1px solid #cbd5e1;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  color: inherit;
}
.api-key-input-wrap {
  display: flex;
  gap: 8px;
}
.api-key-input {
  flex: 1;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-family: monospace;
  font-size: 0.85rem;
  outline: none;
}
.dark .api-key-input {
  background: #282a36;
  border-color: #475569;
}
.copy-key-btn {
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  color: inherit;
}
.dark .copy-key-btn {
  background: #282a36;
  border-color: #475569;
}
.copy-key-btn.primary {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
}

/* Danger Zone */
.danger-box {
  border: 1px solid #fecaca;
  background: #fef2f2;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.dark .danger-box {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
}
.danger-action-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}
.danger-row-title {
  font-size: 0.95rem;
  font-weight: 800;
  color: #991b1b;
}
.dark .danger-row-title {
  color: #fca5a5;
}
.danger-row-desc {
  font-size: 0.78rem;
  color: #7f1d1d;
  margin: 2px 0 0 0;
}
.dark .danger-row-desc {
  color: #f87171;
}
.danger-btn {
  background: #ef4444;
  color: #ffffff;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.danger-btn:hover {
  background: #dc2626;
  transform: translateY(-1px);
}
.danger-btn.outline {
  background: none;
  border: 1px solid #ef4444;
  color: #ef4444;
}
.danger-btn.outline:hover {
  background: #ef4444;
  color: #ffffff;
}
.danger-divider {
  height: 1px;
  background: #fecaca;
}
.dark .danger-divider {
  background: rgba(239, 68, 68, 0.25);
}

/* Spinner */
.btn-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
