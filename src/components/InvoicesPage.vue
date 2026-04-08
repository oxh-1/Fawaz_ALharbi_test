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

        <router-link to="/invoices" class="nav-item active">
          <div class="icon-wrapper active-icon">
            <img src="@/assets/Gittax/Invoicesandreports.png" alt="Invoices" class="nav-icon" />
          </div>
          <span class="active-text">{{ $t('notificationSettings.invoicesAndReports') }}</span>
        </router-link>

        <router-link to="/profile" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Home.png" alt="Profile" class="nav-icon" />
          </div>
          <span>Profile</span>
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
          <h2>Invoices</h2>
        </div>
        <div class="right-header">
          <img src="@/assets/Gittax/Notifcation.png" alt="Notification" class="notification-icon" />
          <div class="user-info">
            <img :src="userAvatar || defaultAvatar" alt="Avatar" class="avatar" />
            <span>{{ displayName }}</span>
          </div>
        </div>
      </header>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            :class="['tab-btn', { active: activeTab === tab.key }]"
            @click="activeTab = tab.key">
            {{ tab.label }}
          </button>
        </div>
        <div class="filter-actions">
          <input v-model="searchQuery" type="text" placeholder="Search invoices..." class="search-input" />
          <button class="export-btn">Export PDF</button>
        </div>
      </div>

      <!-- Invoices Table -->
      <section class="invoices-section">
        <div :class="['invoices-table', { dark: isDarkMode }]">
          <div class="table-header">
            <span>Invoice #</span>
            <span>Client</span>
            <span>Date</span>
            <span>Amount</span>
            <span>Status</span>
            <span>Actions</span>
          </div>
          <div
            v-for="invoice in filteredInvoices"
            :key="invoice.id"
            :class="['table-row', { dark: isDarkMode }]">
            <span class="invoice-id">#{{ invoice.id }}</span>
            <span>{{ invoice.client }}</span>
            <span>{{ invoice.date }}</span>
            <span class="invoice-amount">{{ invoice.amount }}</span>
            <span>
              <span :class="['status-badge', invoice.status]">{{ invoice.statusLabel }}</span>
            </span>
            <span>
              <button class="view-btn">View</button>
            </span>
          </div>

          <div v-if="filteredInvoices.length === 0" class="empty-state">
            <p>No invoices found for "{{ searchQuery }}"</p>
          </div>
        </div>
      </section>

      <!-- Summary Cards -->
      <section class="summary-cards">
        <div :class="['summary-card', { dark: isDarkMode }]">
          <div class="summary-label">Total Invoices</div>
          <div class="summary-value">{{ invoices.length }}</div>
        </div>
        <div :class="['summary-card paid', { dark: isDarkMode }]">
          <div class="summary-label">Paid</div>
          <div class="summary-value">{{ invoices.filter(i => i.status === 'paid').length }}</div>
        </div>
        <div :class="['summary-card pending', { dark: isDarkMode }]">
          <div class="summary-label">Pending</div>
          <div class="summary-value">{{ invoices.filter(i => i.status === 'pending').length }}</div>
        </div>
        <div :class="['summary-card overdue', { dark: isDarkMode }]">
          <div class="summary-label">Overdue</div>
          <div class="summary-value">{{ invoices.filter(i => i.status === 'overdue').length }}</div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import '@/assets/styles/NotificationSettings.css';

export default {
  name: 'InvoicesPage',
  data() {
    return {
      displayName: '',
      userAvatar: '',
      isArabic: false,
      activeTab: 'all',
      searchQuery: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      tabs: [
        { key: 'all', label: 'All' },
        { key: 'paid', label: 'Paid' },
        { key: 'pending', label: 'Pending' },
        { key: 'overdue', label: 'Overdue' }
      ],
      invoices: [
        { id: '1001', client: 'Acme Corp', date: '2025-03-01', amount: 'SAR 4,500', status: 'paid', statusLabel: 'Paid' },
        { id: '1002', client: 'Beta LLC', date: '2025-03-10', amount: 'SAR 2,200', status: 'pending', statusLabel: 'Pending' },
        { id: '1003', client: 'Gamma Inc.', date: '2025-02-20', amount: 'SAR 8,750', status: 'overdue', statusLabel: 'Overdue' },
        { id: '1004', client: 'Delta Co.', date: '2025-03-15', amount: 'SAR 1,100', status: 'paid', statusLabel: 'Paid' },
        { id: '1005', client: 'Epsilon Ltd.', date: '2025-03-18', amount: 'SAR 3,300', status: 'pending', statusLabel: 'Pending' },
        { id: '1006', client: 'Zeta Group', date: '2025-01-30', amount: 'SAR 6,600', status: 'overdue', statusLabel: 'Overdue' }
      ]
    };
  },
  computed: {
    ...mapState(['isDarkMode', 'user']),
    filteredInvoices() {
      let list = this.invoices;
      if (this.activeTab !== 'all') {
        list = list.filter(i => i.status === this.activeTab);
      }
      if (this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase();
        list = list.filter(i =>
          i.client.toLowerCase().includes(q) ||
          i.id.includes(q)
        );
      }
      return list;
    }
  },
  mounted() {
    this.isArabic = this.$i18n.locale === 'ar';
    this.loadUser();
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale']),

    loadUser() {
      const user = this.user || JSON.parse(localStorage.getItem('loggedInUser'));
      if (user) {
        this.displayName = user.name || user.username || 'User';
        this.userAvatar = user.picture || '';
      } else {
        this.$router.push('/login');
      }
    },

    toggleLanguage() {
      const newLocale = this.isArabic ? 'ar' : 'en';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
    }
  }
};
</script>

<style scoped>
.filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-tabs {
  display: flex;
  gap: 8px;
}

.tab-btn {
  padding: 8px 20px;
  border-radius: 20px;
  border: 2px solid #e0e4e8;
  background: transparent;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
  color: inherit;
}

.tab-btn.active {
  border-color: #275559;
  background: #275559;
  color: white;
}

.tab-btn:hover:not(.active) {
  border-color: #aaa;
  background: #f5f7fa;
}

.filter-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #ddd;
  font-size: 0.875rem;
  outline: none;
  background: white;
  color: #333;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: #275559;
}

.export-btn {
  padding: 8px 18px;
  border-radius: 8px;
  border: none;
  background: #275559;
  color: white;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.2s;
}

.export-btn:hover {
  background: #1d3d40;
}

.invoices-section {
  margin-bottom: 24px;
}

.invoices-table {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}

.invoices-table.dark {
  background: #2c2c3e;
  box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1.2fr 1fr;
  padding: 14px 20px;
  background: #f5f7fa;
  font-size: 0.78rem;
  font-weight: 700;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.invoices-table.dark .table-header {
  background: #1a1a2e;
  color: #aaa;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1.2fr 1fr;
  padding: 14px 20px;
  align-items: center;
  border-top: 1px solid #f0f2f5;
  font-size: 0.9rem;
  transition: background 0.15s;
}

.table-row.dark {
  border-top-color: #3a3a50;
}

.table-row:hover {
  background: #f9fafb;
}

.table-row.dark:hover {
  background: #35354a;
}

.invoice-id {
  font-weight: 700;
  color: #275559;
}

.invoices-table.dark .invoice-id {
  color: #5ab5be;
}

.invoice-amount {
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
}

.status-badge.paid {
  background: #e6f4ea;
  color: #188038;
}

.status-badge.pending {
  background: #fff3e0;
  color: #e65100;
}

.status-badge.overdue {
  background: #fce8e6;
  color: #c0392b;
}

.view-btn {
  padding: 5px 14px;
  border-radius: 6px;
  border: 1px solid #275559;
  background: transparent;
  color: #275559;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s;
}

.view-btn:hover {
  background: #275559;
  color: white;
}

.empty-state {
  padding: 40px;
  text-align: center;
  color: #aaa;
  font-size: 0.95rem;
}

.summary-cards {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.summary-card {
  flex: 1 1 160px;
  background: white;
  border-radius: 14px;
  padding: 20px 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  border-left: 4px solid #275559;
}

.summary-card.dark {
  background: #2c2c3e;
  box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.summary-card.paid { border-left-color: #188038; }
.summary-card.pending { border-left-color: #e65100; }
.summary-card.overdue { border-left-color: #c0392b; }

.summary-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.summary-value {
  font-size: 2rem;
  font-weight: 700;
  color: #222;
}

.summary-card.dark .summary-value {
  color: #eee;
}
</style>
