<template>
  <div :class="['notification-settings-container', { dark: isDarkMode }]">
    <!-- Sidebar (same as NotificationSettings) -->
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <router-link to="/dashboard">
        <div class="logo-section">
          <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
        </div>
      </router-link>

      <nav class="navigation">
        <router-link to="/dashboard" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Home.png" alt="Home Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.home') }}</span>
        </router-link>

        <router-link to="/notification-settings" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Invoicesandreports.png" alt="Notifications" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.invoicesAndReports') }}</span>
        </router-link>

        <router-link to="/profile" class="nav-item active">
          <div class="icon-wrapper active-icon">
            <img src="@/assets/Gittax/Home.png" alt="Profile" class="nav-icon" />
          </div>
          <span class="active-text">Profile</span>
        </router-link>

        <router-link to="/settings" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Home.png" alt="Settings" class="nav-icon" />
          </div>
          <span>Settings</span>
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
          <h2>My Profile</h2>
        </div>
        <div class="right-header">
          <img src="@/assets/Gittax/Notifcation.png" alt="Notification Icon" class="notification-icon" />
          <div class="user-info">
            <img :src="userAvatar || defaultAvatar" alt="User Avatar" class="avatar" />
            <span>{{ displayName }}</span>
          </div>
        </div>
      </header>

      <!-- Profile Card -->
      <section class="profile-section">
        <div :class="['profile-card', { dark: isDarkMode }]">
          <div class="profile-avatar-area">
            <img :src="userAvatar || defaultAvatar" alt="Profile Picture" class="profile-avatar" />
            <div class="profile-avatar-label">Profile Picture</div>
          </div>
          <div class="profile-details">
            <div class="profile-field">
              <label>Full Name</label>
              <div :class="['field-value', { dark: isDarkMode }]">{{ displayName }}</div>
            </div>
            <div class="profile-field">
              <label>Email Address</label>
              <div :class="['field-value', { dark: isDarkMode }]">{{ userEmail }}</div>
            </div>
            <div class="profile-field">
              <label>Account Type</label>
              <div :class="['field-value', { dark: isDarkMode }]">
                <span :class="['account-badge', isGoogleUser ? 'google' : 'manual']">
                  {{ isGoogleUser ? '🔵 Google Account' : '👤 Manual Account' }}
                </span>
              </div>
            </div>
            <div class="profile-field">
              <label>User ID</label>
              <div :class="['field-value monospace', { dark: isDarkMode }]">{{ userId }}</div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h3>Quick Actions</h3>
          <div class="action-buttons">
            <router-link to="/settings" class="action-btn">
              ⚙️ Go to Settings
            </router-link>
            <router-link to="/notification-settings" class="action-btn">
              🔔 Notifications
            </router-link>
            <button class="action-btn danger" @click="handleLogout">
              🚪 Logout
            </button>
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
  name: 'ProfilePage',
  data() {
    return {
      displayName: '',
      userEmail: '',
      userAvatar: '',
      userId: '',
      isGoogleUser: false,
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
        this.userId = user.id || '—';
        this.isGoogleUser = !!user.picture; // Google users have a picture URL
      } else {
        this.$router.push('/login');
      }
    },

    toggleLanguage() {
      const newLocale = this.isArabic ? 'ar' : 'en';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
    },

    handleLogout() {
      this.logout();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.profile-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 10px 0;
}

.profile-card {
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  display: flex;
  gap: 36px;
  align-items: flex-start;
  box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}

.profile-card.dark {
  background: #2c2c3e;
  box-shadow: 0 2px 16px rgba(0,0,0,0.3);
}

.profile-avatar-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #275559;
  box-shadow: 0 4px 16px rgba(39,85,89,0.2);
}

.profile-avatar-label {
  font-size: 0.75rem;
  color: #888;
}

.profile-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-field label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.field-value {
  font-size: 1rem;
  font-weight: 500;
  padding: 10px 14px;
  border-radius: 8px;
  background: #f5f7fa;
  color: #222;
}

.field-value.dark {
  background: #1a1a2e;
  color: #eee;
}

.field-value.monospace {
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
  color: #555;
}

.field-value.monospace.dark {
  color: #aaa;
}

.account-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.account-badge.google {
  background: #e8f0fe;
  color: #1a73e8;
}

.account-badge.manual {
  background: #e6f4ea;
  color: #188038;
}

.quick-actions {
  background: #fff;
  border-radius: 16px;
  padding: 24px 32px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}

.quick-actions.dark {
  background: #2c2c3e;
}

.quick-actions h3 {
  margin-bottom: 16px;
  font-size: 1rem;
  color: #444;
}

.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 10px 20px;
  border-radius: 10px;
  background: #275559;
  color: white;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.2s, transform 0.1s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.action-btn:hover {
  background: #1d3d40;
  transform: translateY(-1px);
}

.action-btn.danger {
  background: #c0392b;
}

.action-btn.danger:hover {
  background: #962d22;
}
</style>
