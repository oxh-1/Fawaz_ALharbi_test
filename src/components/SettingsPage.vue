<template>
  <div :class="['notification-settings-container', { dark: isDarkMode }]">
    <!-- Sidebar -->
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <router-link to="/dashboard">
        <div class="logo-section">
          <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
        </div>
      </router-link>

      <nav class="navigation">
        <router-link to="/dashboard" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Home.png" alt="Home" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.home') }}</span>
        </router-link>

        <router-link to="/notification-settings" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Invoicesandreports.png" alt="Notifications" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.invoicesAndReports') }}</span>
        </router-link>

        <router-link to="/profile" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Home.png" alt="Profile" class="nav-icon" />
          </div>
          <span>Profile</span>
        </router-link>

        <router-link to="/settings" class="nav-item active">
          <div class="icon-wrapper active-icon">
            <img src="@/assets/Gittax/Home.png" alt="Settings" class="nav-icon" />
          </div>
          <span class="active-text">Settings</span>
        </router-link>
      </nav>

      <div class="settings">
        <div class="language-toggle">
          <label>{{ $t('notificationSettings.language') }}</label>
          <label class="switch">
            <input type="checkbox" v-model="isArabic" @change="toggleLanguage" />
            <span class="slider"></span>
          </label>
        </div>
        <div class="mode-toggle">
          <label>{{ $t('notificationSettings.nightMode') }}</label>
          <label class="switch">
            <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
            <span class="slider"></span>
          </label>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="content">
      <header class="header">
        <div class="left-header">
          <h2>Settings</h2>
        </div>
        <div class="right-header">
          <img src="@/assets/Gittax/Notifcation.png" alt="Notification" class="notification-icon" />
          <div class="user-info">
            <img :src="userAvatar || defaultAvatar" alt="Avatar" class="avatar" />
            <span>{{ displayName }}</span>
          </div>
        </div>
      </header>

      <section class="settings-section">

        <!-- Account Section -->
        <div :class="['settings-group', { dark: isDarkMode }]">
          <h3 class="settings-group-title">👤 Account</h3>
          <div class="settings-row">
            <div class="settings-row-label">
              <span class="settings-row-name">Display Name</span>
              <span class="settings-row-desc">Your visible name across the platform</span>
            </div>
            <div :class="['settings-row-value', { dark: isDarkMode }]">{{ displayName }}</div>
          </div>
          <div class="settings-row">
            <div class="settings-row-label">
              <span class="settings-row-name">Email</span>
              <span class="settings-row-desc">Your registered email address</span>
            </div>
            <div :class="['settings-row-value', { dark: isDarkMode }]">{{ userEmail }}</div>
          </div>
        </div>

        <!-- Appearance Section -->
        <div :class="['settings-group', { dark: isDarkMode }]">
          <h3 class="settings-group-title">🎨 Appearance</h3>
          <div class="settings-row">
            <div class="settings-row-label">
              <span class="settings-row-name">{{ $t('notificationSettings.nightMode') }}</span>
              <span class="settings-row-desc">Toggle between light and dark interface</span>
            </div>
            <label class="switch">
              <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
              <span class="slider"></span>
            </label>
          </div>
        </div>

        <!-- Language Section -->
        <div :class="['settings-group', { dark: isDarkMode }]">
          <h3 class="settings-group-title">🌐 Language</h3>
          <div class="settings-row">
            <div class="settings-row-label">
              <span class="settings-row-name">Interface Language</span>
              <span class="settings-row-desc">Choose your preferred display language</span>
            </div>
            <div class="lang-buttons">
              <button
                :class="['lang-btn', { active: !isArabic }]"
                @click="setLanguage('en')">English</button>
              <button
                :class="['lang-btn', { active: isArabic }]"
                @click="setLanguage('ar')">العربية</button>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div :class="['settings-group danger-zone', { dark: isDarkMode }]">
          <h3 class="settings-group-title">⚠️ Account Actions</h3>
          <div class="settings-row">
            <div class="settings-row-label">
              <span class="settings-row-name">Logout</span>
              <span class="settings-row-desc">Sign out from your current session</span>
            </div>
            <button class="logout-btn" @click="handleLogout">Logout</button>
          </div>
        </div>

      </section>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import '@/assets/styles/NotificationSettings.css';

export default {
  name: 'SettingsPage',
  data() {
    return {
      displayName: '',
      userEmail: '',
      userAvatar: '',
      isArabic: false,
      defaultAvatar: require('@/assets/Gittax/avatar.png')
    };
  },
  computed: {
    ...mapState(['isDarkMode', 'user'])
  },
  mounted() {
    this.isArabic = this.$i18n.locale === 'ar';
    this.loadUser();
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),

    loadUser() {
      const user = this.user || JSON.parse(localStorage.getItem('loggedInUser'));
      if (user) {
        this.displayName = user.name || user.username || 'User';
        this.userEmail = user.email || '—';
        this.userAvatar = user.picture || '';
      } else {
        this.$router.push('/login');
      }
    },

    toggleLanguage() {
      const newLocale = this.isArabic ? 'ar' : 'en';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
    },

    setLanguage(locale) {
      this.isArabic = locale === 'ar';
      this.$i18n.locale = locale;
      this.setLocale(locale);
    },

    handleLogout() {
      this.logout();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.settings-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 10px 0;
}

.settings-group {
  background: #fff;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.settings-group.dark {
  background: #2c2c3e;
  box-shadow: 0 2px 12px rgba(0,0,0,0.25);
}

.settings-group-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #275559;
  margin-bottom: 18px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e8ecf0;
}

.settings-group.dark .settings-group-title {
  color: #5ab5be;
  border-bottom-color: #3a3a50;
}

.settings-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f2f5;
}

.settings-group.dark .settings-row {
  border-bottom-color: #3a3a50;
}

.settings-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.settings-row-label {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.settings-row-name {
  font-size: 0.95rem;
  font-weight: 600;
}

.settings-row-desc {
  font-size: 0.8rem;
  color: #999;
}

.settings-row-value {
  font-size: 0.9rem;
  padding: 7px 14px;
  background: #f5f7fa;
  border-radius: 8px;
  color: #333;
  min-width: 160px;
  text-align: right;
}

.settings-row-value.dark {
  background: #1a1a2e;
  color: #ddd;
}

.lang-buttons {
  display: flex;
  gap: 8px;
}

.lang-btn {
  padding: 8px 18px;
  border-radius: 8px;
  border: 2px solid #e0e4e8;
  background: transparent;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
  color: inherit;
}

.lang-btn.active {
  border-color: #275559;
  background: #275559;
  color: white;
}

.lang-btn:hover:not(.active) {
  border-color: #aaa;
  background: #f5f7fa;
}

.logout-btn {
  padding: 9px 22px;
  border-radius: 8px;
  border: none;
  background: #c0392b;
  color: white;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #962d22;
}

.danger-zone .settings-group-title {
  color: #c0392b;
}

.settings-group.danger-zone.dark .settings-group-title {
  color: #e57373;
}
</style>
