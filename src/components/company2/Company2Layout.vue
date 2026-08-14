<template>
  <div :class="['c2-container', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Sidebar -->
    <aside :class="['c2-sidebar', { dark: isDarkMode }]">
      <div class="c2-logo-section">
        <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
        <div class="c2-brand-meta">
          <h2 class="c2-brand-name">Fawaz Platform</h2>
          <span class="c2-brand-badge">Enterprise Suite</span>
        </div>
      </div>

      <nav class="c2-nav">
        <!-- Return to Main Platform Dashboard -->
        <router-link to="/dashboard" class="c2-nav-link portal-link">
          <span class="c2-nav-icon-text">🏠</span>
          <span>Main Platform Hub</span>
        </router-link>

        <!-- Company 3 Stakes Terminal Link -->
        <router-link to="/c3/stocks" class="c2-nav-link c3-nav-link">
          <span class="c2-nav-icon-text">📈</span>
          <span>Company 3 (Stakes)</span>
        </router-link>

        <!-- Company 4 Real Estate Hub Link -->
        <router-link to="/c4/properties" class="c2-nav-link c4-nav-link">
          <span class="c2-nav-icon-text">🏢</span>
          <span>Company 4 (Real Estate)</span>
        </router-link>

        <!-- Company 5 Developer Academy Link -->
        <router-link to="/c5/academy" class="c2-nav-link c5-nav-link">
          <span class="c2-nav-icon-text">🎓</span>
          <span>Company 5 (Dev Academy)</span>
        </router-link>

        <div class="c2-nav-divider"></div>

        <router-link to="/c2/home" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/homeL3.png" class="c2-nav-icon" alt="" />
          <span>Home</span>
        </router-link>

        <!-- Operations & Merchant Management Scopes -->
        <template v-if="canAccess('operations')">
          <router-link to="/c2/merchant" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/profL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.merchant') }}</span>
          </router-link>
          <router-link to="/c2/categories" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/catL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.categories') }}</span>
          </router-link>
          <router-link to="/c2/services" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/SerL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.servicesLink') }}</span>
          </router-link>
          <router-link to="/c2/ads" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/AdsL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.ads') }}</span>
          </router-link>
          <router-link to="/c2/content" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/ContentL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.content') }}</span>
          </router-link>
        </template>

        <!-- Support & Customer Care Scopes -->
        <template v-if="canAccess('support')">
          <router-link to="/c2/booking" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/BookingL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.booking') }}</span>
          </router-link>
          <router-link to="/c2/customers" class="c2-nav-link">
            <span class="c2-nav-icon-text">👥</span>
            <span>Customers</span>
          </router-link>
          <router-link to="/c2/booking-dashboard" class="c2-nav-link customer-portal-link">
            <span class="c2-nav-icon-text">🎟️</span>
            <span>Customer Portal</span>
          </router-link>
          <router-link to="/c2/reviews" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/ReviewsL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.reviews') }}</span>
          </router-link>
          <router-link to="/c2/contact" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/ContactL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.contactUs') }}</span>
          </router-link>
        </template>

        <!-- Accounting & Finance Scopes -->
        <template v-if="canAccess('accounting')">
          <router-link to="/invoices" class="c2-nav-link">
            <span class="c2-nav-icon-text">📑</span>
            <span>Invoices</span>
          </router-link>
          <router-link to="/c2/settlement" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/SerL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.settlement') }}</span>
          </router-link>
          <router-link to="/c2/reports" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/ReportsL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.reports') }}</span>
          </router-link>
          <router-link to="/c2/pricing" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/pricingL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.pricing') }}</span>
          </router-link>
        </template>

        <!-- Super Admin Only -->
        <template v-if="isAdmin">
          <router-link to="/c2/permissions" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/PermisionsL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.permissions') }}</span>
          </router-link>
          <router-link to="/c2/settings" class="c2-nav-link">
            <img src="@/assets/L3bnaIMG/SettingL3.png" class="c2-nav-icon" alt="" />
            <span>{{ $t('notificationSettings.setting') }}</span>
          </router-link>
        </template>
      </nav>

      <div class="c2-sidebar-footer">
        <div class="c2-toggle-row">
          <span>{{ isDarkMode ? '☀️ Light Mode' : '🌙 Dark Mode' }}</span>
          <label class="c2-switch">
            <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
        <div class="c2-toggle-row">
          <span>🌐 {{ isArabic ? 'العربية' : 'English' }}</span>
          <label class="c2-switch">
            <input type="checkbox" :checked="isArabic" @change="switchLang" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="c2-main">
      <!-- Top Header -->
      <header class="c2-header">
        <div class="header-left">
          <div class="breadcrumb">
            <router-link to="/dashboard" class="bc-root">Platform Hub</router-link>
            <span class="bc-sep">/</span>
            <span class="bc-curr">Company 2</span>
            <span class="bc-sep">/</span>
            <span class="bc-page">{{ pageTitle }}</span>
          </div>
          <h1 class="c2-header-title">{{ pageTitle }}</h1>
        </div>

        <div class="c2-header-right">
          <!-- Quick Hub Back Button -->
          <router-link to="/dashboard" class="c2-hub-btn" title="Back to Dashboard Hub">
            🏠 Hub
          </router-link>

          <!-- Chat Trigger -->
          <button class="c2-icon-btn" @click="isChatOpen = !isChatOpen" title="Open Live Chat">
            <img src="@/assets/L3bnaIMG/ChatL3.png" class="c2-notif-icon" alt="Chat" />
            <span class="online-indicator"></span>
          </button>

          <!-- Notifications -->
          <button class="c2-icon-btn" @click="showNotifDropdown = !showNotifDropdown" title="Notifications">
            <img src="@/assets/L3bnaIMG/notficationL3.png" class="c2-notif-icon" alt="Notifications" />
          </button>

          <!-- User Menu Pill with Dropdown -->
          <div class="c2-user-wrapper">
            <div class="c2-user-pill" @click="showUserDropdown = !showUserDropdown">
              <img :src="userAvatar || defaultAvatar" alt="Avatar" class="c2-avatar" />
              <div class="c2-user-details">
                <span class="c2-username">{{ displayName }}</span>
                <span class="c2-role-tag">{{ isAdmin ? 'Super Admin' : 'Manager' }}</span>
              </div>
              <span class="dropdown-caret">▾</span>
            </div>

            <div v-if="showUserDropdown" class="c2-user-dropdown" @click.self="showUserDropdown = false">
              <router-link to="/profile" class="c2-dropdown-item" @click.native="showUserDropdown = false">
                👤 My Profile
              </router-link>
              <router-link to="/settings" class="c2-dropdown-item" @click.native="showUserDropdown = false">
                ⚙️ Platform Settings
              </router-link>
              <div class="dropdown-divider"></div>
              <button class="c2-dropdown-item logout" @click="handleLogout">
                🚪 Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content Slot -->
      <div class="c2-body">
        <slot></slot>
      </div>
    </div>
    
    <C2ChatWidget :isOpen="isChatOpen" @close="isChatOpen = false" />
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import '@/assets/styles/company2.css';
import C2ChatWidget from './components/C2ChatWidget.vue';

export default {
  name: 'Company2Layout',
  components: { C2ChatWidget },
  props: {
    pageTitle: {
      type: String,
      default: 'Company 2'
    }
  },
  data() {
    return {
      displayName: '',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      isArabic: false,
      isChatOpen: false,
      showUserDropdown: false,
      showNotifDropdown: false,
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
    },
    isAdmin() {
      if (!this.currentUser) return true;
      return this.currentUser.is_super_admin || this.currentUser.email === 'admin@company2.com' || this.currentUser.email === 'fawazalharbi04@gmail.com';
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),

    canAccess(scope) {
      if (this.isAdmin) return true;
      if (!this.currentUser) return true;
      const email = (this.currentUser.email || '').toLowerCase();
      const roles = this.currentUser.roles || [];

      if (scope === 'accounting') {
        return email.includes('accountant') || roles.includes('accountant');
      }
      if (scope === 'support') {
        return email.includes('support') || roles.includes('support');
      }
      if (scope === 'operations') {
        return email.includes('operations') || roles.includes('operations');
      }
      return false;
    },
    switchLang() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
      this.isArabic = !this.isArabic;
      document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
      document.body.classList.toggle('rtl', this.isArabic);
    },
    async handleLogout() {
      try {
        await this.logout();
      } finally {
        this.$router.push('/login');
      }
    }
  },
  mounted() {
    this.isArabic = this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
    document.body.classList.toggle('rtl', this.isArabic);
    if (this.currentUser) {
      this.displayName = this.currentUser.name || this.currentUser.username || 'Super Admin';
      this.userAvatar = this.currentUser.picture || this.currentUser.avatar || '';
    }
  }
};
</script>

<style scoped>
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  margin-bottom: 2px;
  color: var(--c2-text-muted);
}
.bc-root {
  color: var(--c2-accent);
  text-decoration: none;
  font-weight: 600;
}
.bc-sep {
  opacity: 0.5;
}
.bc-curr {
  font-weight: 500;
}
.bc-page {
  font-weight: 700;
  color: var(--c2-text);
}

.c2-brand-meta {
  display: flex;
  flex-direction: column;
}
.c2-brand-badge {
  font-size: 0.65rem;
  font-weight: 700;
  color: var(--c2-accent);
  text-transform: uppercase;
}

.c2-nav-icon-text {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.c2-nav-divider {
  height: 1px;
  background: var(--c2-border);
  margin: 8px 16px;
}

.portal-link {
  background: rgba(0, 170, 255, 0.08);
  color: var(--c2-accent) !important;
  font-weight: 700 !important;
  border-radius: 8px;
  margin: 0 8px 6px 8px;
}

.customer-portal-link {
  color: #8b5cf6 !important;
  font-weight: 700 !important;
}

.c2-hub-btn {
  background: rgba(0, 170, 255, 0.1);
  color: var(--c2-accent);
  border: 1px solid rgba(0, 170, 255, 0.25);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.825rem;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
}
.c2-hub-btn:hover {
  background: var(--c2-accent);
  color: #ffffff;
  transform: translateY(-1px);
}

.c2-icon-btn {
  background: rgba(0, 0, 0, 0.03);
  border: 1px solid var(--c2-border);
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}
.dark .c2-icon-btn {
  background: rgba(255, 255, 255, 0.05);
}
.c2-icon-btn:hover {
  background: rgba(0, 170, 255, 0.15);
  transform: scale(1.05);
}

.online-indicator {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 8px;
  height: 8px;
  background: var(--c2-success);
  border-radius: 50%;
  border: 2px solid var(--c2-sidebar-bg);
}

.c2-user-wrapper {
  position: relative;
}

.c2-user-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--c2-card-bg);
  border: 1px solid var(--c2-border);
  padding: 4px 12px 4px 6px;
  border-radius: 30px;
  cursor: pointer;
  box-shadow: var(--c2-shadow);
  transition: all 0.2s;
}
.c2-user-pill:hover {
  border-color: var(--c2-accent);
}

.c2-user-details {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.rtl .c2-user-details {
  text-align: right;
}

.c2-role-tag {
  font-size: 0.65rem;
  color: var(--c2-accent);
  font-weight: 700;
}

.dropdown-caret {
  font-size: 0.8rem;
  opacity: 0.6;
}

.c2-user-dropdown {
  position: absolute;
  top: 115%;
  right: 0;
  background: var(--c2-card-bg);
  border: 1px solid var(--c2-border);
  border-radius: var(--c2-radius);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  min-width: 170px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  z-index: 200;
}
.rtl .c2-user-dropdown {
  right: auto;
  left: 0;
}

.c2-dropdown-item {
  padding: 8px 12px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--c2-text);
  text-decoration: none;
  border-radius: var(--c2-radius-sm);
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  transition: all 0.15s;
}
.rtl .c2-dropdown-item {
  text-align: right;
}
.c2-dropdown-item:hover {
  background: rgba(0, 170, 255, 0.1);
  color: var(--c2-accent);
}
.c2-dropdown-item.logout {
  color: var(--c2-danger);
}
.c2-dropdown-item.logout:hover {
  background: rgba(231, 76, 60, 0.1);
}

.dropdown-divider {
  height: 1px;
  background: var(--c2-border);
  margin: 4px 0;
}
</style>
