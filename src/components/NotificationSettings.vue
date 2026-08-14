<template>
  <div :class="['notif-page-root', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="notif-navbar">
      <div class="nav-left">
        <router-link to="/dashboard" class="brand-link">
          <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
          <span class="brand-name">Fawaz Platform</span>
        </router-link>
        <span class="nav-divider">/</span>
        <span class="nav-page-badge">🔔 Notification & Alerts Center</span>
      </div>

      <div class="nav-right">
        <router-link to="/dashboard" class="nav-pill-btn">🏠 Hub</router-link>
        <router-link to="/c2/home" class="nav-pill-btn">🏢 Company 2</router-link>
        <router-link to="/settings" class="nav-pill-btn">⚙️ Settings</router-link>

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

        <button class="nav-logout-btn" @click="handleLogout" title="Logout">
          🚪 Logout
        </button>
      </div>
    </header>

    <!-- Main Body Container -->
    <main class="notif-main-container">
      <!-- Header Banner -->
      <div class="notif-header-banner">
        <div>
          <h1 class="notif-main-title">🔔 Notification Rules & Alert Delivery</h1>
          <p class="notif-subtitle">
            Manage automated customer notifications, SMS gateways, real-time settlement alerts, and AI platform updates.
          </p>
        </div>
        <div class="header-action-group">
          <button class="action-btn-primary" @click="saveAllRules" :disabled="isSaving">
            <span v-if="isSaving" class="btn-spinner"></span>
            <span v-else>💾 Save Notification Rules</span>
          </button>
          <button class="action-btn-secondary" @click="showTestimonialPopup = true">
            ⭐ Add Platform Review
          </button>
        </div>
      </div>

      <!-- KPI Overview Cards -->
      <div class="notif-kpi-grid">
        <div class="notif-kpi-card">
          <div class="kpi-icon-wrap blue">🔔</div>
          <div class="kpi-meta">
            <span class="kpi-value">8 Rules</span>
            <span class="kpi-label">Active Notification Triggers</span>
          </div>
        </div>
        <div class="notif-kpi-card">
          <div class="kpi-icon-wrap green">📧</div>
          <div class="kpi-meta">
            <span class="kpi-value">99.8%</span>
            <span class="kpi-label">Email Delivery Rate</span>
          </div>
        </div>
        <div class="notif-kpi-card">
          <div class="kpi-icon-wrap purple">📱</div>
          <div class="kpi-meta">
            <span class="kpi-value">STC Gateway</span>
            <span class="kpi-label">SMS Alerts Connected</span>
          </div>
        </div>
        <div class="notif-kpi-card">
          <div class="kpi-icon-wrap orange">🤖</div>
          <div class="kpi-meta">
            <span class="kpi-value">AI Live Sync</span>
            <span class="kpi-label">Voice & Smart Briefings</span>
          </div>
        </div>
      </div>

      <!-- Success Notification Alert -->
      <div v-if="saveSuccess" class="success-banner">
        ✅ Notification rules and alert preferences have been successfully updated and saved!
      </div>

      <!-- Notification Rules Groups -->
      <div class="notif-sections-grid">
        <!-- 1. Customer & Booking Alerts -->
        <div class="notif-card">
          <div class="card-header-bar">
            <div class="card-title-group">
              <span class="card-icon">🎟️</span>
              <div>
                <h2 class="card-title">Customer & Booking Triggers</h2>
                <p class="card-desc">Instant alerts sent when customer bookings are placed, confirmed or rescheduled.</p>
              </div>
            </div>
          </div>

          <div class="notif-toggle-list">
            <div class="toggle-row" v-for="item in bookingTriggers" :key="item.id">
              <div class="toggle-meta">
                <span class="toggle-title">{{ item.title }}</span>
                <span class="toggle-desc">{{ item.desc }}</span>
              </div>
              <label class="switch-toggle">
                <input type="checkbox" v-model="item.enabled" @change="onToggleChange" />
                <span class="slider-round"></span>
              </label>
            </div>
          </div>
        </div>

        <!-- 2. Financial & Settlement Alerts -->
        <div class="notif-card">
          <div class="card-header-bar">
            <div class="card-title-group">
              <span class="card-icon">💰</span>
              <div>
                <h2 class="card-title">Financial & Merchant Settlements</h2>
                <p class="card-desc">Alerts for invoice generation, merchant commission payouts, and payment failures.</p>
              </div>
            </div>
          </div>

          <div class="notif-toggle-list">
            <div class="toggle-row" v-for="item in financialTriggers" :key="item.id">
              <div class="toggle-meta">
                <span class="toggle-title">{{ item.title }}</span>
                <span class="toggle-desc">{{ item.desc }}</span>
              </div>
              <label class="switch-toggle">
                <input type="checkbox" v-model="item.enabled" @change="onToggleChange" />
                <span class="slider-round"></span>
              </label>
            </div>
          </div>
        </div>

        <!-- 3. Security, System & AI Broadcasts -->
        <div class="notif-card full-width">
          <div class="card-header-bar">
            <div class="card-title-group">
              <span class="card-icon">🔒</span>
              <div>
                <h2 class="card-title">Security Alerts & AI System Briefings</h2>
                <p class="card-desc">Authentication anomaly warnings, AI daily platform performance summaries, and backup status.</p>
              </div>
            </div>
          </div>

          <div class="notif-toggle-list grid-2">
            <div class="toggle-row" v-for="item in systemTriggers" :key="item.id">
              <div class="toggle-meta">
                <span class="toggle-title">{{ item.title }}</span>
                <span class="toggle-desc">{{ item.desc }}</span>
              </div>
              <label class="switch-toggle">
                <input type="checkbox" v-model="item.enabled" @change="onToggleChange" />
                <span class="slider-round"></span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Testimonials & Reviews Section -->
      <section class="testimonials-container">
        <div class="testimonials-header">
          <div>
            <h2 class="testimonials-title">🌟 Platform Client Feedback & Testimonials</h2>
            <p class="testimonials-sub">Real client reviews submitted for merchants and platform services.</p>
          </div>
          <button class="add-feedback-btn" @click="showTestimonialPopup = true">
            ✍️ Submit Feedback
          </button>
        </div>

        <div class="testimonials-cards-grid">
          <div v-for="test in testimonials" :key="test.id" class="testimonial-card">
            <div class="testimonial-top">
              <div class="author-avatar">
                {{ test.username ? test.username.charAt(0).toUpperCase() : 'U' }}
              </div>
              <div class="author-info">
                <span class="author-name">{{ test.username }}</span>
                <span class="author-rating">★★★★★</span>
              </div>
            </div>
            <p class="testimonial-content">"{{ test.content }}"</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Modal for adding review -->
    <AddTestimonial
      v-if="showTestimonialPopup"
      @close="showTestimonialPopup = false"
      @save="handleSaveTestimonial"
    />
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AddTestimonial from './AddTestimonial.vue';
import { testimonialApi } from '@/services/api';

export default {
  name: 'NotificationSettings',
  components: { AddTestimonial },
  data() {
    return {
      displayName: 'Fawaz Alharbi',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      showTestimonialPopup: false,
      isArabic: false,
      isSaving: false,
      saveSuccess: false,
      bookingTriggers: [
        { id: 'b1', title: '📧 New Booking Email to Merchant', desc: 'Notify merchants immediately when an appointment is booked', enabled: true },
        { id: 'b2', title: '📱 SMS Appointment Confirmation to Client', desc: 'Send booking details and time reminder to client mobile', enabled: true },
        { id: 'b3', title: '⚠️ Booking Reschedule / Cancellation Alert', desc: 'Notify platform admins when appointments are modified', enabled: true },
        { id: 'b4', title: '⭐ Post-Service Review Request', desc: 'Automatically request 5-star feedback 2 hours after service completion', enabled: true },
      ],
      financialTriggers: [
        { id: 'f1', title: '💵 Merchant Settlement Payout Confirmation', desc: 'Send SMS and email when merchant payout transfer is approved', enabled: true },
        { id: 'f2', title: '📑 Monthly Tax Invoice & Statement Generation', desc: 'Deliver PDF VAT invoice at the start of each month', enabled: true },
        { id: 'f3', title: '⚠️ High-Value Transaction Flag (> SAR 5,000)', desc: 'Alert Super Admin for manual security verification', enabled: false },
        { id: 'f4', title: '📉 Commission Threshold Warnings', desc: 'Notify when outstanding settlement exceeds 30 days', enabled: true },
      ],
      systemTriggers: [
        { id: 's1', title: '🤖 Fawaz AI Daily Morning Summary', desc: 'Receive audio & visual performance briefing every morning at 9:00 AM', enabled: true },
        { id: 's2', title: '🔒 Unrecognized Login & IP Alert', desc: 'Instant security notice on logins from unrecognized devices', enabled: true },
        { id: 's3', title: '💾 Database Backup & Export Completion', desc: 'Notify upon automated cloud snapshot and CSV archive generation', enabled: true },
        { id: 's4', title: '⚡ REST API Rate-Limit Alerts', desc: 'Warning when developer webhook failures exceed 5 attempts', enabled: false },
      ],
      testimonials: [
        { id: 1, username: 'Fawaz Alharbi', content: 'Fawaz Platform has simplified our operations tremendously. Fast booking management and instant settlements!' },
        { id: 2, username: 'Sara Al-Otaibi', content: 'The AI Support Assistant and notification system keep our staff on top of every customer appointment.' },
        { id: 3, username: 'Khalid Al-Mansoor', content: 'Outstanding platform performance and seamless bilingual experience for both our Arabic and English staff.' }
      ]
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
      this.userAvatar = this.currentUser.picture || this.currentUser.avatar || '';
    }

    // Load persisted triggers if available
    const saved = localStorage.getItem('fawaz_notif_rules');
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        if (parsed.booking)   this.bookingTriggers   = parsed.booking;
        if (parsed.financial) this.financialTriggers = parsed.financial;
        if (parsed.system)    this.systemTriggers    = parsed.system;
      } catch (e) {
        console.warn('Failed to parse notif rules', e);
      }
    }

    this.fetchTestimonials();
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

    onToggleChange() {
      // Auto-persist on toggle
      localStorage.setItem('fawaz_notif_rules', JSON.stringify({
        booking:   this.bookingTriggers,
        financial: this.financialTriggers,
        system:    this.systemTriggers
      }));
    },

    saveAllRules() {
      this.isSaving = true;
      this.saveSuccess = false;
      this.onToggleChange();

      setTimeout(() => {
        this.isSaving = false;
        this.saveSuccess = true;
        setTimeout(() => { this.saveSuccess = false; }, 3000);
      }, 400);
    },

    async fetchTestimonials() {
      try {
        const res = await testimonialApi.list();
        if (Array.isArray(res) && res.length > 0) {
          this.testimonials = res.map(t => ({
            id: t.id,
            username: t.user?.name || t.user?.username || t.name || 'Client',
            content: t.content || t.comment || t.message
          }));
        }
      } catch (e) {
        console.warn('Testimonials load fallback', e);
      }
    },

    handleSaveTestimonial(newReview) {
      this.showTestimonialPopup = false;
      if (newReview) {
        this.testimonials.unshift({
          id: Date.now(),
          username: newReview.username || this.displayName,
          content: newReview.content || newReview.message
        });
      }
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
    }
  }
};
</script>

<style scoped>
.notif-page-root {
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.notif-page-root.dark {
  background: #0f172a;
  color: #f8fafc;
}
.notif-page-root.rtl {
  direction: rtl;
}

/* Navbar */
.notif-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.dark .notif-navbar {
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

/* Main Body */
.notif-main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px 60px;
}

.notif-header-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.notif-main-title {
  font-size: 1.75rem;
  font-weight: 900;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}
.notif-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
}
.dark .notif-subtitle {
  color: #94a3b8;
}

.header-action-group {
  display: flex;
  gap: 10px;
}

.action-btn-primary {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
  transition: all 0.2s;
}
.action-btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
}

.action-btn-secondary {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
  padding: 10px 18px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.dark .action-btn-secondary {
  background: #181824;
  border-color: #475569;
  color: #e2e8f0;
}
.action-btn-secondary:hover {
  background: #f1f5f9;
}

/* KPI Grid */
.notif-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}
.notif-kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
.dark .notif-kpi-card {
  background: #181824;
  border-color: #2d3748;
}

.kpi-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
}
.kpi-icon-wrap.blue   { background: rgba(2, 132, 199, 0.15); }
.kpi-icon-wrap.green  { background: rgba(16, 185, 129, 0.15); }
.kpi-icon-wrap.purple { background: rgba(139, 92, 246, 0.15); }
.kpi-icon-wrap.orange { background: rgba(245, 158, 11, 0.15); }

.kpi-meta {
  display: flex;
  flex-direction: column;
}
.kpi-value {
  font-size: 1.1rem;
  font-weight: 800;
}
.kpi-label {
  font-size: 0.75rem;
  color: #64748b;
}
.dark .kpi-label {
  color: #94a3b8;
}

/* Success Banner */
.success-banner {
  background: #dcfce7;
  color: #166534;
  padding: 12px 18px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 0.875rem;
  font-weight: 700;
}

/* Sections Grid */
.notif-sections-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}
@media (max-width: 900px) {
  .notif-sections-grid {
    grid-template-columns: 1fr;
  }
}
.full-width {
  grid-column: 1 / -1;
}

.notif-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.dark .notif-card {
  background: #181824;
  border-color: #2d3748;
}

.card-header-bar {
  margin-bottom: 18px;
}
.card-title-group {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.card-icon {
  font-size: 1.5rem;
}
.card-title {
  font-size: 1.1rem;
  font-weight: 800;
  margin: 0 0 2px 0;
}
.card-desc {
  font-size: 0.78rem;
  color: #64748b;
  margin: 0;
}
.dark .card-desc {
  color: #94a3b8;
}

.notif-toggle-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.notif-toggle-list.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 750px) {
  .notif-toggle-list.grid-2 {
    grid-template-columns: 1fr;
  }
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  gap: 12px;
}
.dark .toggle-row {
  background: #1e1e2e;
  border-color: #2d3748;
}

.toggle-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.toggle-title {
  font-size: 0.85rem;
  font-weight: 700;
}
.toggle-desc {
  font-size: 0.72rem;
  color: #64748b;
}
.dark .toggle-desc {
  color: #94a3b8;
}

/* Switch Toggle */
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

/* Testimonials */
.testimonials-container {
  margin-top: 40px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
}
.dark .testimonials-container {
  background: #181824;
  border-color: #2d3748;
}

.testimonials-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.testimonials-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0 0 2px 0;
}
.testimonials-sub {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
}
.dark .testimonials-sub {
  color: #94a3b8;
}

.add-feedback-btn {
  background: #0284c7;
  color: #ffffff;
  border: none;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.add-feedback-btn:hover {
  background: #0369a1;
}

.testimonials-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}
.testimonial-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px;
}
.dark .testimonial-card {
  background: #1e1e2e;
  border-color: #2d3748;
}

.testimonial-top {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.author-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0284c7, #0369a1);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.9rem;
}
.author-info {
  display: flex;
  flex-direction: column;
}
.author-name {
  font-size: 0.85rem;
  font-weight: 700;
}
.author-rating {
  font-size: 0.75rem;
  color: #f59e0b;
}
.testimonial-content {
  font-size: 0.8rem;
  color: #475569;
  line-height: 1.4;
  margin: 0;
}
.dark .testimonial-content {
  color: #cbd5e1;
}

/* Spinner */
.btn-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
