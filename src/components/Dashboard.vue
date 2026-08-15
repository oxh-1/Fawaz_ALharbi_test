<template>
  <div :class="['platform-dashboard', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="dash-navbar">
      <div class="navbar-left">
        <img src="@/assets/Gittax/logo1.png" alt="Platform Logo" class="nav-logo" />
        <div class="nav-brand">
          <span class="nav-title">Fawaz Platform</span>
          <span class="nav-badge">v2.5 Enterprise</span>
        </div>
      </div>

      <div class="navbar-right">
        <button class="nav-action-btn" @click="toggleDarkMode" :title="isDarkMode ? 'Light Mode' : 'Dark Mode'">
          {{ isDarkMode ? '☀️' : '🌙' }}
        </button>
        <button class="nav-action-btn" @click="toggleLanguage" :title="'Language Switcher'">
          🌐 {{ isArabic ? 'English' : 'العربية' }}
        </button>
        
        <div class="user-profile-menu">
          <img :src="currentUserAvatar" alt="Avatar" class="user-avatar" />
          <div class="user-meta">
            <span class="user-name">{{ currentUserName }}</span>
            <span class="user-role">{{ isAdmin ? '👑 Super Admin' : '👤 Business User' }}</span>
          </div>
          <button class="logout-btn" @click="handleLogout" title="Logout">
            🚪
          </button>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="dash-main">
      <!-- Hero Welcome Section -->
      <section class="dash-hero">
        <div class="hero-header-row">
          <div class="hero-text">
            <div class="hero-status-pill">
              <span class="status-dot"></span>
              <span>All Systems Operational • Unified 5-Company Multi-Tenant Hub</span>
            </div>
            <h1 class="hero-greeting">{{ $t('dashboard.welcome') }}, {{ currentUserName }}! 👋</h1>
            <p class="hero-subtext">
              Welcome to the Fawaz Unified Enterprise Hub. Monitor platform activities, manage accounting & tax invoices, launch merchant operations, track stock intelligence, stake tokenized real estate Sukuk, and access free developer academies with a single click.
            </p>
          </div>
        </div>

        <div class="hero-actions-container">
          <span class="hero-actions-label">⚡ Direct Company Launchpads:</span>
          <div class="hero-actions">
            <router-link to="/c2/home" class="hero-btn primary">
              <span class="h-icon">🚀</span>
              <span>Company 2 (Services)</span>
            </router-link>
            <router-link to="/c3/stocks" class="hero-btn c3-btn">
              <span class="h-icon">📈</span>
              <span>Company 3 (Stocks AI)</span>
            </router-link>
            <router-link to="/c4/properties" class="hero-btn c4-btn">
              <span class="h-icon">🏢</span>
              <span>Company 4 (Real Estate)</span>
            </router-link>
            <a href="mailto:me@fawazalharbi.dev" class="hero-btn contact-me-btn" title="Send email to me@fawazalharbi.dev">
              <span class="h-icon">✉️</span>
              <span>Contact Me</span>
            </a>
            <router-link to="/c5/academy" class="hero-btn c5-btn">
              <span class="h-icon">🎓</span>
              <span>Company 5 (Dev Academy)</span>
            </router-link>
            <router-link to="/invoices" class="hero-btn invoices-btn">
              <span class="h-icon">📑</span>
              <span>Invoices & Billing</span>
            </router-link>
            <router-link to="/c2/booking-dashboard" class="hero-btn secondary">
              <span class="h-icon">🎟️</span>
              <span>Customer Portal</span>
            </router-link>
          </div>
        </div>
      </section>

      <!-- KPI Summary Cards -->
      <section class="dash-kpi-grid">
        <div class="kpi-card" v-for="(kpi, idx) in kpis" :key="idx">
          <div class="kpi-icon-wrapper" :style="{ backgroundColor: kpi.bg }">
            <span class="kpi-icon">{{ kpi.icon }}</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">{{ kpi.label }}</span>
            <strong class="kpi-value">{{ kpi.value }}</strong>
            <span class="kpi-trend positive">{{ kpi.trend }}</span>
          </div>
        </div>
      </section>

      <!-- Section Title -->
      <div class="section-header">
        <h2 class="section-title">🏢 Core Platform Workspaces</h2>
        <span class="section-subtitle">Select a module to manage records, settings, and business flows</span>
      </div>

      <!-- Application Modules Grid -->
      <section class="modules-grid">
        <div
          v-for="mod in displayedModules"
          :key="mod.id"
          class="module-card"
          @click="navigateTo(mod.route)"
        >
          <div class="module-card-header">
            <div class="module-icon" :style="{ background: mod.color }">
              {{ mod.icon }}
            </div>
            <span class="module-tag" :class="mod.badgeType">{{ mod.badge }}</span>
          </div>
          <div class="module-card-body">
            <h3 class="module-title">{{ mod.title }}</h3>
            <p class="module-desc">{{ mod.description }}</p>
          </div>
          <div class="module-card-footer">
            <span class="module-action-link">Open Module →</span>
          </div>
        </div>
      </section>

      <!-- Quick Platform Activity & System Status -->
      <section class="bottom-grid">
        <div class="activity-box">
          <div class="box-header">
            <h3 class="box-title">⚡ Live Activity Feed</h3>
            <span class="live-indicator">Live Stream</span>
          </div>
          <ul class="activity-list">
            <li class="activity-item">
              <span class="act-dot green"></span>
              <div class="act-content">
                <p><strong>New Booking #BK-1089</strong> was confirmed for Salon Prime</p>
                <span class="act-time">5 minutes ago</span>
              </div>
            </li>
            <li class="activity-item">
              <span class="act-dot blue"></span>
              <div class="act-content">
                <p><strong>Settlement #ST-402</strong> completed for SAR 4,850.00</p>
                <span class="act-time">22 minutes ago</span>
              </div>
            </li>
            <li class="activity-item">
              <span class="act-dot purple"></span>
              <div class="act-content">
                <p><strong>Merchant "Oasis Cafe"</strong> updated business hours and services</p>
                <span class="act-time">1 hour ago</span>
              </div>
            </li>
            <li class="activity-item">
              <span class="act-dot gold"></span>
              <div class="act-content">
                <p><strong>Verified Review</strong> 5.0 ★ submitted by Customer</p>
                <span class="act-time">2 hours ago</span>
              </div>
            </li>
          </ul>
        </div>

        <div class="system-status-box">
          <div class="box-header">
            <h3 class="box-title">🛡️ System & Tenant Status</h3>
            <span class="status-badge-ok">All Systems Operational</span>
          </div>
          <div class="status-rows">
            <div class="status-row">
              <span>Cloudflare Workers API</span>
              <span class="status-tag online">Edge 99.99%</span>
            </div>
            <div class="status-row">
              <span>Cloudflare D1 (fawaz_db)</span>
              <span class="status-tag online">Connected ✓</span>
            </div>
            <div class="status-row">
              <span>Google SSO & OAuth2</span>
              <span class="status-tag ready">Ready</span>
            </div>
            <div class="status-row">
              <span>Multi-Tenant Context</span>
              <span class="status-tag tenant">Tenant #1 (Active)</span>
            </div>
            <div class="status-row">
              <span>IP Tracking</span>
              <span class="status-tag online">CF-Connecting-IP ✓</span>
            </div>
          </div>
          <div class="system-actions">
            <button class="status-btn" @click="navigateTo('settings')">⚙️ System Preferences</button>
            <button class="status-btn" @click="navigateTo('c2/reports')">📊 Export Audit Log</button>
          </div>
        </div>

        <!-- Admin-only: User IP Monitor -->
        <div v-if="isAdmin" class="ip-monitor-box">
          <div class="box-header">
            <h3 class="box-title">🌐 User IP Monitor</h3>
            <span class="admin-only-badge">Admin Only</span>
          </div>
          <div v-if="isLoadingUsers" class="ip-loading">Loading user data...</div>
          <div v-else-if="adminUsers.length === 0" class="ip-empty">No user login data available yet.</div>
          <div v-else class="ip-table-wrapper">
            <table class="ip-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Last IP</th>
                  <th>Last Login</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in adminUsers" :key="u.id" class="ip-row">
                  <td>
                    <div class="ip-user-cell">
                      <span v-if="u.is_super_admin" class="ip-admin-crown">👑</span>
                      {{ u.name }}
                    </div>
                  </td>
                  <td class="ip-email">{{ u.email }}</td>
                  <td>
                    <code class="ip-address" :class="{ 'ip-unknown': !u.last_login_ip }">{{ u.last_login_ip || '—' }}</code>
                  </td>
                  <td class="ip-date">{{ formatDate(u.last_login_at) }}</td>
                  <td>
                    <span class="ip-status-tag" :class="'ip-status-' + u.status">{{ u.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>

    <!-- Floating AI Support Assistant Button -->
    <button class="floating-ai-btn" @click="isChatOpen = !isChatOpen" title="Fawaz AI Support Assistant">
      <span class="ai-btn-icon">🤖</span>
      <span class="ai-btn-label">AI Support</span>
      <span class="ai-live-badge">Online</span>
    </button>

    <!-- AI Chat Widget Component -->
    <C2ChatWidget :isOpen="isChatOpen" @close="isChatOpen = false" />
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import { merchantApi, bookingApi, settlementApi, adminApi } from '@/services/api';
import C2ChatWidget from './company2/components/C2ChatWidget.vue';

export default {
  name: 'UserDashboard',
  components: { C2ChatWidget },
  data() {
    return {
      isChatOpen: false,
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      adminUsers: [],
      isLoadingUsers: false,
      kpis: [
        { label: 'Active Merchants', value: '10', trend: '+12% this month', icon: '🏪', bg: 'rgba(0, 170, 255, 0.15)' },
        { label: 'Monthly Bookings', value: '185', trend: '+28% growth', icon: '🎟️', bg: 'rgba(147, 51, 234, 0.15)' },
        { label: 'Settled Revenue', value: 'SAR 94,850', trend: '+15.4% vs last qtr', icon: '💰', bg: 'rgba(16, 185, 129, 0.15)' },
        { label: 'Client Satisfaction', value: '4.9 ★', trend: 'Based on 150+ reviews', icon: '⭐', bg: 'rgba(245, 158, 11, 0.15)' },
      ],
      modules: [
        {
          id: 'c2-hub',
          title: 'Company 2 Operations Hub',
          description: 'Merchants, Services, Bookings, Categories, Content CMS, Ads & Permissions.',
          route: 'c2/home',
          icon: '🏢',
          color: 'linear-gradient(135deg, #0284c7, #0369a1)',
          badge: 'Featured',
          badgeType: 'badge-blue',
          scopes: ['operations', 'all']
        },
        {
          id: 'c3-terminal',
          title: 'Company 3: Stakes & Market Terminal',
          description: 'Track stock market stakes, screen unusual lower volumes, and discover companies at lowest price ever.',
          route: 'c3/stocks',
          icon: '📈',
          color: 'linear-gradient(135deg, #10b981, #047857)',
          badge: 'Stakes AI',
          badgeType: 'badge-green',
          scopes: ['accounting', 'operations', 'all']
        },
        {
          id: 'c4-proptech',
          title: 'Company 4: Real Estate & Sukuk Vault',
          description: 'Tokenized fractional commercial properties, Grade-A Riyadh towers, and automated 11.8% annual rental dividend yields.',
          route: 'c4/properties',
          icon: '💎',
          color: 'linear-gradient(135deg, #d97706, #b45309)',
          badge: 'PropTech RWA',
          badgeType: 'badge-orange',
          scopes: ['accounting', 'operations', 'all']
        },
        {
          id: 'c5-academy',
          title: 'Company 5: Developer Academy',
          description: '140+ Free certified coding courses, FullStack frameworks, PyTorch AI tracks, cheat sheets, and live playground.',
          route: 'c5/academy',
          icon: '🎓',
          color: 'linear-gradient(135deg, #6366f1, #4f46e5)',
          badge: '100% Free',
          badgeType: 'badge-indigo',
          scopes: ['operations', 'support', 'accounting', 'all']
        },
        {
          id: 'booking-portal',
          title: 'Customer Booking Portal',
          description: 'Interactive self-service appointments, rescheduling, cancellations and reviews.',
          route: 'c2/booking-dashboard',
          icon: '🎟️',
          color: 'linear-gradient(135deg, #8b5cf6, #6d28d9)',
          badge: 'Support',
          badgeType: 'badge-purple',
          scopes: ['support', 'all']
        },
        {
          id: 'invoices',
          title: 'Invoices & Billing Manager',
          description: 'View invoices, generate billing statements, track payment statuses and print receipts.',
          route: 'invoices',
          icon: '📑',
          color: 'linear-gradient(135deg, #10b981, #059669)',
          badge: 'Finance',
          badgeType: 'badge-green',
          scopes: ['accounting', 'all']
        },
        {
          id: 'reports',
          title: 'Analytics & Financial Reports',
          description: 'Live charts, commission summaries, merchant performance and CSV exports.',
          route: 'c2/reports',
          icon: '📊',
          color: 'linear-gradient(135deg, #f59e0b, #d97706)',
          badge: 'Finance',
          badgeType: 'badge-orange',
          scopes: ['accounting', 'all']
        },
        {
          id: 'notifications',
          title: 'Notification Settings',
          description: 'System alert rules, email notification preferences, and push configurations.',
          route: 'notification-settings',
          icon: '🔔',
          color: 'linear-gradient(135deg, #ec4899, #be185d)',
          badge: 'Alerts',
          badgeType: 'badge-pink',
          scopes: ['support', 'accounting', 'all']
        },
        {
          id: 'profile',
          title: 'Account & Security Profile',
          description: 'Manage personal profile, avatar, passwords, API tokens and active sessions.',
          route: 'profile',
          icon: '👤',
          color: 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
          badge: 'Account',
          badgeType: 'badge-blue',
          scopes: ['all']
        },
        {
          id: 'settings',
          title: 'Platform System Settings',
          description: 'Appearance preferences, multi-lingual settings, backup and security policies.',
          route: 'settings',
          icon: '⚙️',
          color: 'linear-gradient(135deg, #64748b, #334155)',
          badge: 'System',
          badgeType: 'badge-gray',
          scopes: ['operations', 'all']
        },
        {
          id: 'testimonials',
          title: 'Add Testimonial & Feedback',
          description: 'Submit client ratings, verified reviews and business success stories.',
          route: 'fawaz',
          icon: '🌟',
          color: 'linear-gradient(135deg, #eab308, #ca8a04)',
          badge: 'Public',
          badgeType: 'badge-yellow',
          scopes: ['all']
        },
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
    },
    displayedModules() {
      if (this.isAdmin) return this.modules;
      const email = (this.currentUser?.email || '').toLowerCase();
      const roles = this.currentUser?.roles || [];

      let userScope = 'all';
      if (email.includes('accountant') || roles.includes('accountant')) userScope = 'accounting';
      if (email.includes('support') || roles.includes('support')) userScope = 'support';
      if (email.includes('operations') || roles.includes('operations')) userScope = 'operations';

      return this.modules.filter(m => !m.scopes || m.scopes.includes(userScope) || m.scopes.includes('all'));
    },
    currentUserName() {
      if (!this.currentUser) return 'Admin User';
      return this.currentUser.name || this.currentUser.username || 'Admin User';
    },
    currentUserAvatar() {
      if (this.currentUser && (this.currentUser.avatar || this.currentUser.picture)) {
        return this.currentUser.avatar || this.currentUser.picture;
      }
      return this.defaultAvatar;
    },
    isAdmin() {
      if (!this.currentUser) return true;
      return (
        this.currentUser.is_super_admin ||
        this.currentUser.role === 'admin' ||
        this.currentUser.email === 'admin@company2.com' ||
        this.currentUser.email === 'me@fawazalharbi.dev'
      );
    },
    isArabic() {
      return this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    }
  },
  async mounted() {
    // Load live KPI counts from backend
    try {
      const [m, b, s] = await Promise.all([
        merchantApi.list().catch(() => null),
        bookingApi.list().catch(() => null),
        settlementApi.list().catch(() => null),
      ]);
      if (m && Array.isArray(m.data || m)) {
        const count = (m.data || m).length;
        if (count > 0) this.kpis[0].value = `${count}`;
      }
      if (b && Array.isArray(b.data || b)) {
        const count = (b.data || b).length;
        if (count > 0) this.kpis[1].value = `${count}`;
      }
      if (s && Array.isArray(s.data || s)) {
        const list = s.data || s;
        const total = list.reduce((acc, curr) => acc + Number(curr.amount || 0), 0);
        if (total > 0) {
          this.kpis[2].value = `SAR ${total.toLocaleString('en-US', { minimumFractionDigits: 0 })}`;
        }
      }
    } catch (e) {
      console.warn('Dashboard KPI stats fallback to default demo numbers');
    }

    // Load user IP data for admin
    if (this.isAdmin) {
      this.fetchAdminUsers();
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),
    navigateTo(route) {
      this.$router.push(`/${route}`);
    },
    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.setLocale(newLocale);
      if (this.$i18n) {
        this.$i18n.locale = newLocale;
      }
    },
    async handleLogout() {
      try {
        await this.logout();
      } finally {
        this.$router.push('/login');
      }
    },
    async fetchAdminUsers() {
      this.isLoadingUsers = true;
      try {
        const res = await adminApi.users({ limit: 20 });
        const users = res?.data?.data || res?.data || res || [];
        this.adminUsers = Array.isArray(users) ? users : [];
      } catch (e) {
        console.warn('Could not load admin user list:', e?.message);
        this.adminUsers = [];
      } finally {
        this.isLoadingUsers = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '—';
      try {
        return new Date(dateStr).toLocaleString('en-US', {
          month: 'short', day: 'numeric', year: 'numeric',
          hour: '2-digit', minute: '2-digit',
        });
      } catch { return dateStr; }
    },
  }
};
</script>

<style scoped>
.platform-dashboard {
  min-height: 100vh;
  background-color: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  transition: background-color 0.3s, color 0.3s;
}

.platform-dashboard.dark {
  background-color: #0f172a;
  color: #f8fafc;
}

.platform-dashboard.rtl {
  direction: rtl;
}

/* Navbar */
.dash-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 32px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 100;
}
.dark .dash-navbar {
  background: #1e1e2e;
  border-bottom-color: #2d3748;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-logo {
  height: 38px;
  width: auto;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-title {
  font-size: 1.2rem;
  font-weight: 800;
  letter-spacing: -0.3px;
}

.nav-badge {
  background: rgba(2, 132, 199, 0.1);
  color: #0284c7;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
}
.dark .nav-badge {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-action-btn {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  color: inherit;
  transition: all 0.2s;
}
.dark .nav-action-btn {
  background: #282a36;
  border-color: #334155;
}
.nav-action-btn:hover {
  background: #e2e8f0;
}
.dark .nav-action-btn:hover {
  background: #383a4c;
}

.user-profile-menu {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 30px;
  padding: 4px 10px 4px 6px;
}
.dark .user-profile-menu {
  background: #181824;
  border-color: #2d3748;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.user-meta {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.rtl .user-meta {
  text-align: right;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 700;
  line-height: 1.1;
}

.user-role {
  font-size: 0.7rem;
  color: #64748b;
}
.dark .user-role {
  color: #94a3b8;
}

.logout-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: transform 0.2s;
}
.logout-btn:hover {
  transform: scale(1.15);
}

/* Main Container */
.dash-main {
  max-width: 1280px;
  margin: 0 auto;
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

/* Hero Section — Ultra Responsive & Glassmorphic */
.dash-hero {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 40%, #0c4a6e 100%);
  border-radius: 24px;
  padding: 32px 36px;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 24px;
  box-shadow: 0 14px 34px -10px rgba(2, 132, 199, 0.4);
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.dark .dash-hero {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 60%, #020617 100%);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 14px 34px -10px rgba(0, 0, 0, 0.6);
}

.hero-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 0.76rem;
  font-weight: 700;
  margin-bottom: 12px;
  backdrop-filter: blur(8px);
}
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 8px #10b981;
  display: inline-block;
}

.hero-text {
  max-width: 920px;
  text-align: left;
}
.rtl .hero-text {
  text-align: right;
}

.hero-greeting {
  font-size: 1.95rem;
  font-weight: 900;
  margin: 0 0 8px 0;
  letter-spacing: -0.5px;
  line-height: 1.25;
}

.hero-subtext {
  font-size: 0.94rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.55;
  margin: 0;
  max-width: 900px;
}

.hero-actions-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  padding-top: 18px;
}
.hero-actions-label {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255, 255, 255, 0.85);
}

.hero-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 10px;
  width: 100%;
}

.hero-btn {
  padding: 11px 16px;
  border-radius: 12px;
  font-size: 0.84rem;
  font-weight: 800;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.hero-btn:hover {
  transform: translateY(-2px);
  filter: brightness(1.05);
}

.h-icon {
  font-size: 1rem;
}

.hero-btn.primary {
  background: #ffffff;
  color: #0369a1;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
}
.dark .hero-btn.primary {
  background: #f8fafc;
  color: #0f172a;
}

.hero-btn.c3-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
}

.hero-btn.c4-btn {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  color: #ffffff;
}

.hero-btn.c5-btn {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
}

.hero-btn.invoices-btn {
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  color: #ffffff;
}

.hero-btn.secondary {
  background: rgba(255, 255, 255, 0.14);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}
.hero-btn.secondary:hover {
  background: rgba(255, 255, 255, 0.24);
}

.badge-indigo {
  background: #e0e7ff;
  color: #3730a3;
}

@media (max-width: 768px) {
  .dash-hero {
    padding: 24px 20px;
    border-radius: 18px;
  }
  .hero-greeting {
    font-size: 1.5rem;
  }
  .hero-actions {
    grid-template-columns: 1fr;
  }
}

/* KPI Grid */
.dash-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 20px;
}

.kpi-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  transition: transform 0.2s, box-shadow 0.2s;
}
.dark .kpi-card {
  background: #1e1e2e;
  border-color: #2d3748;
}
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.kpi-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon {
  font-size: 1.5rem;
}

.kpi-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.rtl .kpi-info {
  text-align: right;
}

.kpi-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 600;
}
.dark .kpi-label {
  color: #94a3b8;
}

.kpi-value {
  font-size: 1.4rem;
  font-weight: 800;
  line-height: 1.2;
  margin: 2px 0;
}

.kpi-trend {
  font-size: 0.72rem;
  font-weight: 600;
}
.kpi-trend.positive {
  color: #10b981;
}

/* Section Header */
.section-header {
  text-align: left;
}
.rtl .section-header {
  text-align: right;
}

.section-title {
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0 0 4px 0;
}

.section-subtitle {
  font-size: 0.875rem;
  color: #64748b;
}
.dark .section-subtitle {
  color: #94a3b8;
}

/* Modules Grid */
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.module-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.25s ease;
  position: relative;
  overflow: hidden;
}
.dark .module-card {
  background: #1e1e2e;
  border-color: #2d3748;
}
.module-card:hover {
  transform: translateY(-4px);
  border-color: #0284c7;
  box-shadow: 0 12px 28px rgba(2, 132, 199, 0.12);
}
.dark .module-card:hover {
  border-color: #38bdf8;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
}

.module-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.module-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.module-tag {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 10px;
  text-transform: uppercase;
}
.badge-blue   { background: #e0f2fe; color: #0369a1; }
.badge-purple { background: #f3e8ff; color: #7e22ce; }
.badge-green  { background: #dcfce7; color: #15803d; }
.badge-orange { background: #fef3c7; color: #b45309; }
.badge-pink   { background: #fce7f3; color: #be185d; }
.badge-gray   { background: #f1f5f9; color: #475569; }
.badge-yellow { background: #fef9c3; color: #a16207; }

.dark .badge-blue   { background: rgba(56, 189, 248, 0.2); color: #38bdf8; }
.dark .badge-purple { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
.dark .badge-green  { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
.dark .badge-orange { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.dark .badge-pink   { background: rgba(236, 72, 153, 0.2); color: #f472b6; }
.dark .badge-gray   { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; }
.dark .badge-yellow { background: rgba(234, 179, 8, 0.2); color: #fde047; }

.module-card-body {
  text-align: left;
  margin-bottom: 20px;
}
.rtl .module-card-body {
  text-align: right;
}

.module-title {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 6px 0;
}

.module-desc {
  font-size: 0.825rem;
  color: #64748b;
  line-height: 1.4;
  margin: 0;
}
.dark .module-desc {
  color: #94a3b8;
}

.module-card-footer {
  text-align: right;
  border-top: 1px solid #f1f5f9;
  padding-top: 12px;
}
.rtl .module-card-footer {
  text-align: left;
}
.dark .module-card-footer {
  border-top-color: #282a36;
}

.module-action-link {
  font-size: 0.8rem;
  font-weight: 700;
  color: #0284c7;
}
.dark .module-action-link {
  color: #38bdf8;
}

/* Bottom Grid */
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  align-items: start;
}
@media (max-width: 900px) {
  .bottom-grid {
    grid-template-columns: 1fr;
  }
}

.activity-box, .system-status-box {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.dark .activity-box, .dark .system-status-box {
  background: #1e1e2e;
  border-color: #2d3748;
}

.box-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.box-title {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
}

.live-indicator {
  font-size: 0.72rem;
  font-weight: 700;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 3px 8px;
  border-radius: 8px;
}

.activity-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  text-align: left;
}
.rtl .activity-item {
  text-align: right;
}

.act-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}
.act-dot.green  { background: #10b981; }
.act-dot.blue   { background: #0284c7; }
.act-dot.purple { background: #8b5cf6; }
.act-dot.gold   { background: #f59e0b; }

.act-content p {
  margin: 0 0 2px 0;
  font-size: 0.85rem;
  line-height: 1.3;
}

.act-time {
  font-size: 0.72rem;
  color: #94a3b8;
}

/* System status box */
.status-badge-ok {
  font-size: 0.75rem;
  font-weight: 700;
  color: #10b981;
}

.status-rows {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.825rem;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 8px;
}
.dark .status-row {
  background: #181824;
}

.status-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 6px;
}
.status-tag.online { background: #dcfce7; color: #166534; }
.status-tag.ready  { background: #e0f2fe; color: #0369a1; }
.status-tag.tenant { background: #fef3c7; color: #92400e; }
.dark .status-tag.online { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
.dark .status-tag.ready  { background: rgba(56, 189, 248, 0.2); color: #38bdf8; }
.dark .status-tag.tenant { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

.system-actions {
  display: flex;
  gap: 10px;
}

.status-btn {
  flex: 1;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  color: inherit;
  transition: all 0.2s;
}
.dark .status-btn {
  background: #282a36;
  border-color: #334155;
}
.status-btn:hover {
  background: #f1f5f9;
}
.dark .status-btn:hover {
  background: #383a4c;
}

/* Floating AI Assistant Button */
.floating-ai-btn {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  border-radius: 30px;
  padding: 10px 18px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(2, 132, 199, 0.35);
  transition: all 0.25s ease;
  z-index: 999;
}
.rtl .floating-ai-btn {
  right: auto;
  left: 24px;
}
.floating-ai-btn:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 12px 30px rgba(2, 132, 199, 0.45);
}

.ai-btn-icon {
  font-size: 1.25rem;
}

.ai-btn-label {
  font-size: 0.875rem;
  font-weight: 800;
  letter-spacing: -0.2px;
}

.ai-live-badge {
  background: #10b981;
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  text-transform: uppercase;
}

/* Responsive */
@media (max-width: 800px) {
  .dash-hero {
    flex-direction: column;
    text-align: center;
    gap: 20px;
    padding: 24px;
  }
  .hero-text {
    text-align: center;
  }
  .hero-actions {
    flex-direction: column;
    width: 100%;
  }
  .dash-navbar {
    padding: 12px 16px;
  }
}

/* ─── Contact Me Button ──────────────────────────────────────────────────── */
.hero-btn.contact-me-btn {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: #ffffff;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 14px rgba(6, 182, 212, 0.3);
}
.hero-btn.contact-me-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(6, 182, 212, 0.45);
  filter: brightness(1.08);
}

/* ─── IP Monitor Box ─────────────────────────────────────────────────────── */
.ip-monitor-box {
  grid-column: 1 / -1;
  background: #ffffff;
  border-radius: 16px;
  padding: 20px 24px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}
.dark .ip-monitor-box {
  background: #1e1e2e;
  border-color: #2d3748;
}

.admin-only-badge {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 10px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.ip-loading,
.ip-empty {
  padding: 16px 0;
  color: #64748b;
  font-size: 0.875rem;
  text-align: center;
}
.dark .ip-loading,
.dark .ip-empty {
  color: #94a3b8;
}

.ip-table-wrapper {
  overflow-x: auto;
  margin-top: 12px;
}

.ip-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.83rem;
}

.ip-table thead th {
  text-align: left;
  padding: 8px 12px;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
  border-bottom: 2px solid #e2e8f0;
}
.dark .ip-table thead th {
  color: #94a3b8;
  border-bottom-color: #2d3748;
}

.ip-row {
  transition: background 0.15s;
}
.ip-row:hover {
  background: #f8fafc;
}
.dark .ip-row:hover {
  background: rgba(255,255,255,0.04);
}

.ip-table tbody td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.dark .ip-table tbody td {
  border-bottom-color: #1a1a2e;
}

.ip-user-cell {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
}

.ip-admin-crown {
  font-size: 0.9rem;
}

.ip-email {
  color: #64748b;
  font-size: 0.8rem;
}
.dark .ip-email {
  color: #94a3b8;
}

.ip-address {
  background: #0f172a;
  color: #4ade80;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.78rem;
  padding: 2px 8px;
  border-radius: 6px;
  letter-spacing: 0.3px;
}
.dark .ip-address {
  background: #0d1117;
  color: #4ade80;
}
.ip-address.ip-unknown {
  background: #f1f5f9;
  color: #94a3b8;
}
.dark .ip-address.ip-unknown {
  background: #1e293b;
}

.ip-date {
  font-size: 0.78rem;
  color: #64748b;
  white-space: nowrap;
}
.dark .ip-date {
  color: #94a3b8;
}

.ip-status-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 8px;
  text-transform: capitalize;
}
.ip-status-active   { background: #dcfce7; color: #166534; }
.ip-status-inactive { background: #fef3c7; color: #92400e; }
.ip-status-banned   { background: #fee2e2; color: #991b1b; }
.dark .ip-status-active   { background: rgba(34,197,94,0.15);  color: #4ade80; }
.dark .ip-status-inactive { background: rgba(245,158,11,0.15); color: #fbbf24; }
.dark .ip-status-banned   { background: rgba(239,68,68,0.15);  color: #f87171; }
</style>
