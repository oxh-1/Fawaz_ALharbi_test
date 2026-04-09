<template>
  <Company2Layout page-title="Settings">
    <!-- Tabs -->
    <div class="c2-tabs">
      <button v-for="tab in tabs" :key="tab.key" :class="['c2-tab', { active: activeTab === tab.key }]" @click="activeTab = tab.key">
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Company Info -->
    <div v-if="activeTab === 'company'" class="c2-card">
      <h3 class="c2-card-title">🏢 Company Information</h3>
      <div class="c2-grid-2">
        <div class="c2-form-group"><label class="c2-form-label">Company Name</label><input v-model="company.name" class="c2-form-input" /></div>
        <div class="c2-form-group"><label class="c2-form-label">Website</label><input v-model="company.website" class="c2-form-input" placeholder="https://..." /></div>
        <div class="c2-form-group"><label class="c2-form-label">Email</label><input v-model="company.email" type="email" class="c2-form-input" /></div>
        <div class="c2-form-group"><label class="c2-form-label">Phone</label><input v-model="company.phone" class="c2-form-input" placeholder="+966..." /></div>
        <div class="c2-form-group" style="grid-column:1/-1"><label class="c2-form-label">Address</label><textarea v-model="company.address" class="c2-form-textarea" style="min-height:60px"></textarea></div>
      </div>
      <button class="c2-btn c2-btn-primary" @click="save('company')">Save Company Info</button>
    </div>

    <!-- Appearance -->
    <div v-if="activeTab === 'appearance'" class="c2-card">
      <h3 class="c2-card-title">🎨 Appearance</h3>
      <div class="settings-row-list">
        <div class="settings-row">
          <div><div class="settings-row-name">Dark Mode</div><div class="settings-row-desc">Toggle dark/light interface</div></div>
          <label class="c2-switch"><input type="checkbox" :checked="isDarkMode" @change="$store.dispatch('toggleDarkMode')" /><span class="c2-switch-slider"></span></label>
        </div>
        <div class="settings-row">
          <div><div class="settings-row-name">Primary Color</div><div class="settings-row-desc">Main accent color</div></div>
          <input type="color" v-model="appearance.primaryColor" style="width:40px;height:36px;border:none;border-radius:6px;cursor:pointer" />
        </div>
        <div class="settings-row">
          <div><div class="settings-row-name">Language</div><div class="settings-row-desc">Interface display language</div></div>
          <div style="display:flex;gap:8px">
            <button :class="['c2-btn c2-btn-sm', !isAr ? 'c2-btn-primary' : 'c2-btn-ghost']" @click="setLang('en')">English</button>
            <button :class="['c2-btn c2-btn-sm', isAr ? 'c2-btn-primary' : 'c2-btn-ghost']" @click="setLang('ar')">العربية</button>
          </div>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" style="margin-top:16px" @click="save('appearance')">Save Appearance</button>
    </div>

    <!-- Notifications -->
    <div v-if="activeTab === 'notifications'" class="c2-card">
      <h3 class="c2-card-title">🔔 Notification Preferences</h3>
      <div class="settings-row-list">
        <div class="settings-row" v-for="notif in notifications" :key="notif.key">
          <div><div class="settings-row-name">{{ notif.label }}</div><div class="settings-row-desc">{{ notif.desc }}</div></div>
          <label class="c2-switch"><input type="checkbox" v-model="notif.enabled" /><span class="c2-switch-slider"></span></label>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" style="margin-top:16px" @click="save('notifications')">Save Notifications</button>
    </div>

    <!-- Security -->
    <div v-if="activeTab === 'security'" class="c2-card">
      <h3 class="c2-card-title">🔐 Security Settings</h3>
      <div class="c2-form-group"><label class="c2-form-label">Current Password</label><input type="password" v-model="security.current" class="c2-form-input" placeholder="••••••••" /></div>
      <div class="c2-form-group"><label class="c2-form-label">New Password</label><input type="password" v-model="security.newPass" class="c2-form-input" placeholder="••••••••" /></div>
      <div class="c2-form-group"><label class="c2-form-label">Confirm New Password</label><input type="password" v-model="security.confirm" class="c2-form-input" placeholder="••••••••" /></div>
      <div class="settings-row-list" style="margin-top:20px">
        <div class="settings-row">
          <div><div class="settings-row-name">Two-Factor Auth</div><div class="settings-row-desc">Add extra security with OTP</div></div>
          <label class="c2-switch"><input type="checkbox" v-model="security.twoFactor" /><span class="c2-switch-slider"></span></label>
        </div>
      </div>
      <button class="c2-btn c2-btn-primary" style="margin-top:16px" @click="save('security')">Update Password</button>
    </div>

    <p v-if="savedTab" style="color:#27ae60;margin-top:12px;font-weight:600;font-size:0.875rem">✓ {{ savedTab }} settings saved!</p>
  </Company2Layout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Settings',
  components: { Company2Layout },
  data() {
    return {
      activeTab: 'company', savedTab: '', isAr: false,
      tabs: [
        { key: 'company', label: 'Company', icon: '🏢' },
        { key: 'appearance', label: 'Appearance', icon: '🎨' },
        { key: 'notifications', label: 'Notifications', icon: '🔔' },
        { key: 'security', label: 'Security', icon: '🔐' }
      ],
      company: { name: 'Company 2', website: 'https://company2.sa', email: 'info@company2.sa', phone: '+966500000000', address: 'Riyadh, Saudi Arabia' },
      appearance: { primaryColor: '#00aaff' },
      notifications: [
        { key: 'new_booking', label: 'New Booking', desc: 'Notify when a new booking is created', enabled: true },
        { key: 'new_review', label: 'New Review', desc: 'Notify when a review is submitted', enabled: true },
        { key: 'payment', label: 'Payment Received', desc: 'Notify on settlement payouts', enabled: true },
        { key: 'merchant_reg', label: 'Merchant Registration', desc: 'New merchant sign-up notifications', enabled: false }
      ],
      security: { current: '', newPass: '', confirm: '', twoFactor: false }
    };
  },
  computed: { ...mapState(['isDarkMode']) },
  methods: {
    ...mapActions(['setLocale']),
    setLang(l) { this.isAr = l === 'ar'; this.$i18n.locale = l; this.setLocale(l); },
    save(tab) { this.savedTab = tab.charAt(0).toUpperCase() + tab.slice(1); setTimeout(() => (this.savedTab = ''), 2500); }
  }
};
</script>

<style scoped>
.settings-row-list { display: flex; flex-direction: column; gap: 0; }
.settings-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--c2-border, #e8ecf0); }
.settings-row:last-child { border-bottom: none; }
.settings-row-name { font-size: 0.9rem; font-weight: 600; }
.settings-row-desc { font-size: 0.78rem; color: var(--c2-text-muted, #7f8c8d); margin-top: 2px; }
</style>
