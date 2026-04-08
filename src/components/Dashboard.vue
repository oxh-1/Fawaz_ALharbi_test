<template>
  <div :class="['dashboard-container', { dark: isDarkMode }]">
    <div class="header">
      <h2>{{ $t('dashboard.title') }}</h2>
      <div class="user-section">
        <div class="user-info" v-if="currentUser">
          <img :src="currentUser.picture || defaultAvatar" alt="User Avatar" class="avatar" />
          <span class="username-label">{{ currentUser.name || currentUser.username }}</span>
        </div>
        <div class="actions">
          <button @click="toggleDarkMode">{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</button>
          <button @click="toggleLanguage">{{ isArabic ? 'English' : 'اللغة العربية' }}</button>
          <button @click="handleLogout">{{ $t('dashboard.logout') }}</button>
        </div>
      </div>
    </div>

    <p class="welcome-text">{{ $t('dashboard.welcome') }}, {{ currentUser ? (currentUser.name || currentUser.username) : '' }}</p>

    <div class="companies">
      <div class="company-card" @click="navigateTo('notification-settings')">
        <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Gittax Platform</h3>
          <p>Notification Settings & Reports</p>
          <span class="card-link">{{ $t('dashboard.notificationSettings') }} →</span>
        </div>
      </div>

      <div class="company-card" @click="navigateTo('reports')">
        <img src="@/assets/Gittax/logo1.png" alt="Company Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Company Reports</h3>
          <p>View and export your reports</p>
          <span class="card-link">{{ $t('dashboard.reports') }} →</span>
        </div>
      </div>

      <div class="company-card" @click="navigateTo('profile')">
        <img src="@/assets/Gittax/logo1.png" alt="Profile" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>My Profile</h3>
          <p>View and edit your profile</p>
          <span class="card-link">Go to Profile →</span>
        </div>
      </div>

      <div class="company-card" @click="navigateTo('settings')">
        <img src="@/assets/Gittax/logo1.png" alt="Settings" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Settings</h3>
          <p>App preferences and language</p>
          <span class="card-link">Go to Settings →</span>
        </div>
      </div>

      <div class="company-card" @click="navigateTo('invoices')">
        <img src="@/assets/Gittax/logo1.png" alt="Invoices" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Invoices</h3>
          <p>Manage your invoices and billing</p>
          <span class="card-link">Go to Invoices →</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'UserDashboard',
  data() {
    return {
      isArabic: this.$i18n.locale === 'ar',
      defaultAvatar: require('@/assets/Gittax/avatar.png')
    };
  },
  computed: {
    ...mapState(['isDarkMode', 'user']),
    currentUser() {
      // Prefer Vuex store, fallback to localStorage
      return this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    }
  },
  created() {
    // Guard: redirect to login if not authenticated
    if (!this.currentUser) {
      this.$router.push('/login');
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'logout']),
    navigateTo(route) {
      this.$router.push(`/${route}`);
    },
    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.$i18n.locale = newLocale;
      this.isArabic = !this.isArabic;
    },
    handleLogout() {
      this.logout();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  padding: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f0f2f5;
  color: black;
  min-height: 100vh;
}

.dashboard-container.dark {
  background-color: #1a1a2e;
  color: white;
}

.header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  background: white;
  border-radius: 12px;
  padding: 16px 24px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  box-sizing: border-box;
}

.dashboard-container.dark .header {
  background: #16213e;
  box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

.user-section {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #275559;
}

.username-label {
  font-weight: 600;
  font-size: 0.95rem;
}

.actions {
  display: flex;
  gap: 10px;
}

.actions button {
  padding: 8px 16px;
  cursor: pointer;
  border: none;
  border-radius: 8px;
  background-color: #275559;
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background-color 0.2s, transform 0.1s;
}

.actions button:hover {
  background-color: #1d3d40;
  transform: translateY(-1px);
}

.welcome-text {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 30px;
  align-self: flex-start;
}

.dashboard-container.dark .welcome-text {
  color: #aaa;
}

.companies {
  display: flex;
  justify-content: center;
  gap: 24px;
  flex-wrap: wrap;
  width: 100%;
}

.company-card {
  background: white;
  border-radius: 16px;
  padding: 28px 24px;
  width: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}

.dashboard-container.dark .company-card {
  background: #16213e;
  box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

.company-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 16px 32px rgba(0,0,0,0.12);
}

.company-logo {
  width: 72px;
  height: 72px;
  margin-bottom: 14px;
  border-radius: 12px;
}

.company-info {
  text-align: center;
}

.company-info h3 {
  margin-bottom: 6px;
  font-size: 1.05rem;
  font-weight: 700;
}

.company-info p {
  font-size: 0.82rem;
  color: #888;
  margin-bottom: 12px;
}

.dashboard-container.dark .company-info p {
  color: #aaa;
}

.card-link {
  color: #275559;
  font-weight: 600;
  font-size: 0.85rem;
}

.dashboard-container.dark .card-link {
  color: #5ab5be;
}
</style>
