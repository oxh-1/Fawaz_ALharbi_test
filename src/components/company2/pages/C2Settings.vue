<template>
  <Company2Layout page-title="Settings">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Platform Preferences & Settings</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Customize company branding, regional localization, notification channels, and security.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="saveAll" :disabled="saving">
        {{ saving ? 'Saving...' : '💾 Save All Changes' }}
      </button>
    </div>

    <!-- Tabs -->
    <div class="c2-tabs" style="margin-bottom:20px">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['c2-tab', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key">
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Tab 1: Company Info -->
    <div v-if="activeTab === 'company'" class="c2-card">
      <h3 class="c2-card-title">🏢 Company Profile & Tax Information</h3>
      
      <div class="c2-grid-2">
        <div class="c2-form-group">
          <label class="c2-form-label">Platform / Company Name *</label>
          <input v-model="company.name" class="c2-form-input" placeholder="e.g. Fawaz Booking Hub" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Official Website</label>
          <input v-model="company.website" class="c2-form-input" placeholder="https://company2.sa" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Support Email *</label>
          <input v-model="company.email" type="email" class="c2-form-input" placeholder="support@company2.sa" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Support Phone</label>
          <input v-model="company.phone" class="c2-form-input" placeholder="+966 50 123 4567" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Commercial Registration (CR)</label>
          <input v-model="company.cr_number" class="c2-form-input" placeholder="1010123456" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">VAT / Tax ID</label>
          <input v-model="company.vat_number" class="c2-form-input" placeholder="300123456700003" />
        </div>
        <div class="c2-form-group" style="grid-column:1/-1">
          <label class="c2-form-label">Headquarters Address</label>
          <textarea v-model="company.address" class="c2-form-input" rows="2" placeholder="King Fahd Road, Riyadh, Saudi Arabia"></textarea>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" @click="saveTab('Company')">Save Company Info</button>
    </div>

    <!-- Tab 2: Appearance & Localization -->
    <div v-if="activeTab === 'appearance'" class="c2-card">
      <h3 class="c2-card-title">🎨 Appearance, Theme & Language</h3>
      <div class="settings-row-list">
        <div class="settings-row">
          <div>
            <div class="settings-row-name">Dark Mode Theme</div>
            <div class="settings-row-desc">Enable sleek night-mode dark styling across all pages</div>
          </div>
          <label class="c2-switch">
            <input type="checkbox" :checked="isDarkMode" @change="toggleTheme" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-name">Primary Brand Color</div>
            <div class="settings-row-desc">Main accent highlight color for buttons, badges, active tabs</div>
          </div>
          <div style="display:flex;align-items:center;gap:10px">
            <input type="color" v-model="appearance.primary_color" style="width:42px;height:36px;border:none;border-radius:6px;cursor:pointer" />
            <code style="font-size:0.85rem">{{ appearance.primary_color }}</code>
          </div>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-name">Default Language & Text Direction</div>
            <div class="settings-row-desc">Switch between English (LTR) and Arabic (RTL)</div>
          </div>
          <div style="display:flex;gap:8px">
            <button
              :class="['c2-btn c2-btn-sm', currentLang === 'en' ? 'c2-btn-primary' : 'c2-btn-ghost']"
              @click="changeLanguage('en')">
              🇬🇧 English (LTR)
            </button>
            <button
              :class="['c2-btn c2-btn-sm', currentLang === 'ar' ? 'c2-btn-primary' : 'c2-btn-ghost']"
              @click="changeLanguage('ar')">
              🇸🇦 العربية (RTL)
            </button>
          </div>
        </div>

        <div class="settings-row">
          <div>
            <div class="settings-row-name">Currency Display</div>
            <div class="settings-row-desc">Default currency shown in revenue cards and checkout</div>
          </div>
          <select v-model="appearance.currency" class="c2-select" style="width:140px">
            <option value="SAR">SAR (ر.س)</option>
            <option value="USD">USD ($)</option>
            <option value="AED">AED (د.إ)</option>
          </select>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" style="margin-top:20px" @click="saveTab('Appearance')">Save Appearance</button>
    </div>

    <!-- Tab 3: Notifications -->
    <div v-if="activeTab === 'notifications'" class="c2-card">
      <h3 class="c2-card-title">🔔 Notification & Alert Channels</h3>
      <div class="settings-row-list">
        <div class="settings-row" v-for="notif in notificationChannels" :key="notif.key">
          <div>
            <div class="settings-row-name">{{ notif.label }}</div>
            <div class="settings-row-desc">{{ notif.desc }}</div>
          </div>
          <label class="c2-switch">
            <input type="checkbox" v-model="notifications[notif.key]" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" style="margin-top:20px" @click="saveTab('Notifications')">Save Notification Settings</button>
    </div>

    <!-- Tab 4: Security -->
    <div v-if="activeTab === 'security'" class="c2-card">
      <h3 class="c2-card-title">🔐 Account Security & Password</h3>
      
      <div class="settings-row-list" style="margin-bottom:20px">
        <div class="settings-row">
          <div>
            <div class="settings-row-name">Two-Factor Authentication (2FA)</div>
            <div class="settings-row-desc">Require SMS / Authenticator verification upon login</div>
          </div>
          <label class="c2-switch">
            <input type="checkbox" v-model="security.two_factor_auth" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
      </div>

      <div style="border-top:1px solid var(--c2-border);padding-top:20px;max-width:500px">
        <h4 style="font-size:0.95rem;margin-bottom:14px">Change Admin Password</h4>
        <div class="c2-form-group">
          <label class="c2-form-label">Current Password *</label>
          <input type="password" v-model="passwordForm.old_password" class="c2-form-input" placeholder="••••••••" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">New Password *</label>
          <input type="password" v-model="passwordForm.new_password" class="c2-form-input" placeholder="••••••••" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Confirm New Password *</label>
          <input type="password" v-model="passwordForm.new_password_confirmation" class="c2-form-input" placeholder="••••••••" />
        </div>
        <button class="c2-btn c2-btn-primary" @click="changePassword">Update Password</button>
        <p v-if="passError" style="color:var(--c2-danger,#e74c3c);font-size:0.85rem;margin-top:8px">{{ passError }}</p>
      </div>
    </div>

    <p v-if="savedMessage" style="color:var(--c2-success,#27ae60);margin-top:16px;font-weight:600;font-size:0.92rem">
      ✓ {{ savedMessage }}
    </p>
  </Company2Layout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Company2Layout from '../Company2Layout.vue';
import { settingApi } from '@/services/api';

export default {
  name: 'C2Settings',
  components: { Company2Layout },
  data() {
    return {
      activeTab: 'company',
      savedMessage: '',
      saving: false,
      passError: '',
      tabs: [
        { key: 'company', label: 'Company Profile', icon: '🏢' },
        { key: 'appearance', label: 'Appearance & Language', icon: '🎨' },
        { key: 'notifications', label: 'Notifications', icon: '🔔' },
        { key: 'security', label: 'Security & Password', icon: '🔐' }
      ],
      company: {
        name: 'Company 2 Platform',
        website: 'https://company2.sa',
        email: 'support@company2.sa',
        phone: '+966 50 123 4567',
        address: 'King Fahd Road, Riyadh, Saudi Arabia',
        cr_number: '1010123456',
        vat_number: '300123456700003'
      },
      appearance: {
        theme: 'light',
        primary_color: '#00aaff',
        currency: 'SAR',
        font_family: 'Inter'
      },
      notifications: {
        email_enabled: true,
        sms_enabled: true,
        whatsapp_enabled: false,
        new_booking: true,
        booking_cancelled: true,
        review_received: true,
        settlement_alerts: true
      },
      notificationChannels: [
        { key: 'email_enabled', label: 'Email Notifications', desc: 'Receive instant transactional booking and billing emails' },
        { key: 'sms_enabled', label: 'SMS Instant Alerts', desc: 'Send SMS verification OTPs and customer booking reminders' },
        { key: 'whatsapp_enabled', label: 'WhatsApp Business API', desc: 'Automate WhatsApp booking updates directly to customer mobile' },
        { key: 'new_booking', label: 'New Booking Alert', desc: 'Alert admins whenever a new customer reserves an appointment' },
        { key: 'review_received', label: 'Review Moderation Alert', desc: 'Notify admin team when a new customer review is pending' },
        { key: 'settlement_alerts', label: 'Settlement Payout Clearance', desc: 'Weekly automated reconciliation reports for accounting' },
      ],
      security: {
        two_factor_auth: false,
        session_timeout_min: 60
      },
      passwordForm: {
        old_password: '',
        new_password: '',
        new_password_confirmation: ''
      }
    };
  },
  computed: {
    ...mapState(['isDarkMode', 'settings']),
    currentLang() {
      return this.$i18n.locale || 'en';
    }
  },
  async mounted() {
    await this.fetchSettings();
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode']),
    async fetchSettings() {
      try {
        const res = await settingApi.list();
        if (res) {
          if (res.company) this.company = { ...this.company, ...res.company };
          if (res.appearance) this.appearance = { ...this.appearance, ...res.appearance };
          if (res.notifications) this.notifications = { ...this.notifications, ...res.notifications };
          if (res.security) this.security = { ...this.security, ...res.security };
        }
      } catch (e) {
        console.error('Failed to load settings', e);
      }
    },
    toggleTheme() {
      this.toggleDarkMode();
    },
    changeLanguage(lang) {
      this.$i18n.locale = lang;
      this.setLocale(lang);
      document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
      document.body.classList.toggle('rtl', lang === 'ar');
    },
    async saveTab(tabName) {
      await this.saveAll(tabName);
    },
    async saveAll(tabName = 'All') {
      this.saving = true;
      try {
        await settingApi.update({
          company: this.company,
          appearance: this.appearance,
          notifications: this.notifications,
          security: this.security
        });
        this.savedMessage = `${tabName} settings saved successfully!`;
        setTimeout(() => (this.savedMessage = ''), 3000);
      } catch (e) {
        console.error('Failed to save settings', e);
      } finally {
        this.saving = false;
      }
    },
    async changePassword() {
      this.passError = '';
      if (!this.passwordForm.old_password || !this.passwordForm.new_password) {
        this.passError = 'Please enter both current and new passwords.';
        return;
      }
      if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
        this.passError = 'New password and confirmation do not match.';
        return;
      }
      try {
        await settingApi.updatePassword({
          old_password: this.passwordForm.old_password,
          new_password: this.passwordForm.new_password,
          new_password_confirmation: this.passwordForm.new_password_confirmation
        });
        this.savedMessage = 'Password changed successfully!';
        this.passwordForm = { old_password: '', new_password: '', new_password_confirmation: '' };
        setTimeout(() => (this.savedMessage = ''), 3500);
      } catch (e) {
        this.passError = e.message || 'Failed to update password. Verify current password.';
      }
    }
  }
};
</script>

<style scoped>
.settings-row-list {
  display: flex;
  flex-direction: column;
}
.settings-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--c2-border, #e8ecf0);
}
.settings-row:last-child {
  border-bottom: none;
}
.settings-row-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--c2-text);
}
.settings-row-desc {
  font-size: 0.8rem;
  color: var(--c2-text-muted, #7f8c8d);
  margin-top: 2px;
}
</style>
