<template>
  <div :class="['invoices-page-root', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="invoices-navbar">
      <div class="nav-left">
        <router-link to="/dashboard" class="brand-link">
          <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
          <span class="brand-name">Fawaz Platform</span>
        </router-link>
        <span class="nav-divider">/</span>
        <span class="nav-page-badge">📑 Invoices & Billing Center</span>
      </div>

      <div class="nav-right">
        <router-link to="/dashboard" class="nav-pill-btn">🏠 Hub</router-link>
        <router-link to="/c2/home" class="nav-pill-btn">🏢 Company 2</router-link>
        <router-link to="/c3/stocks" class="nav-pill-btn">📈 Company 3</router-link>
        <router-link to="/c4/properties" class="nav-pill-btn">🏢 Company 4</router-link>
        <router-link to="/c5/academy" class="nav-pill-btn">🎓 Company 5</router-link>
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

    <!-- Main Container -->
    <main class="invoices-main-container">
      <!-- Header Banner -->
      <div class="invoices-header-banner">
        <div>
          <h1 class="invoices-main-title">📑 Invoices & Tax Statements</h1>
          <p class="invoices-subtitle">
            Manage official client VAT invoices, track settlement payments, and export compliant tax receipts.
          </p>
        </div>
        <div class="header-action-group">
          <button class="create-invoice-btn" @click="showCreateModal = true">
            ➕ Generate New Invoice
          </button>
          <button class="export-data-btn" @click="exportCSV">
            📥 Export CSV
          </button>
        </div>
      </div>

      <!-- KPI Overview Cards -->
      <div class="invoices-kpi-grid">
        <div class="inv-kpi-card">
          <div class="kpi-icon-wrap blue">💰</div>
          <div class="kpi-meta">
            <span class="kpi-value">SAR {{ totalInvoiced.toLocaleString() }}</span>
            <span class="kpi-label">Total Invoiced Volume</span>
          </div>
        </div>
        <div class="inv-kpi-card">
          <div class="kpi-icon-wrap green">✓</div>
          <div class="kpi-meta">
            <span class="kpi-value">SAR {{ totalPaid.toLocaleString() }}</span>
            <span class="kpi-label">Collected & Paid ({{ paidPercentage }}%)</span>
          </div>
        </div>
        <div class="inv-kpi-card">
          <div class="kpi-icon-wrap orange">⏳</div>
          <div class="kpi-meta">
            <span class="kpi-value">SAR {{ totalPending.toLocaleString() }}</span>
            <span class="kpi-label">Pending Collection</span>
          </div>
        </div>
        <div class="inv-kpi-card">
          <div class="kpi-icon-wrap purple">📑</div>
          <div class="kpi-meta">
            <span class="kpi-value">{{ invoices.length }}</span>
            <span class="kpi-label">Total Generated Invoices</span>
          </div>
        </div>
      </div>

      <!-- Filter Controls Bar -->
      <div class="invoices-filter-bar">
        <div class="status-tabs-wrap">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            :class="['status-tab-btn', { active: activeTab === tab.key }]"
            @click="activeTab = tab.key"
          >
            <span>{{ tab.label }}</span>
            <span class="tab-count-badge">{{ getTabCount(tab.key) }}</span>
          </button>
        </div>

        <div class="search-wrap">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by invoice #, client name, or service..."
            class="search-input-box"
          />
        </div>
      </div>

      <!-- Invoices Data Table -->
      <div class="invoices-table-card">
        <table class="invoices-table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Client / Merchant</th>
              <th>Service Item</th>
              <th>Issue Date</th>
              <th>Due Date</th>
              <th>Amount (SAR)</th>
              <th>Status</th>
              <th style="text-align:center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in filteredInvoices" :key="inv.id">
              <td>
                <span class="invoice-num">{{ inv.number }}</span>
              </td>
              <td>
                <div class="client-cell">
                  <span class="client-name">{{ inv.client }}</span>
                  <span class="client-sub">{{ inv.email }}</span>
                </div>
              </td>
              <td>
                <span class="service-tag">{{ inv.service }}</span>
              </td>
              <td>{{ inv.date }}</td>
              <td>{{ inv.dueDate }}</td>
              <td>
                <strong class="amount-text">SAR {{ inv.amount.toLocaleString() }}</strong>
              </td>
              <td>
                <span :class="['status-badge', inv.status]">
                  {{ inv.status.toUpperCase() }}
                </span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="tbl-action-btn view" @click="viewInvoice(inv)" title="View & Print Invoice">
                    👁️ View
                  </button>
                  <button v-if="inv.status === 'pending'" class="tbl-action-btn pay" @click="markAsPaid(inv)" title="Mark as Paid">
                    ✓ Paid
                  </button>
                  <button class="tbl-action-btn delete" @click="deleteInvoice(inv.id)" title="Delete Invoice">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredInvoices.length === 0">
              <td colspan="8" class="empty-state-cell">
                <span class="empty-icon">🔍</span>
                <p>No invoices matching your search or selected filter.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Modal: View & Print Invoice -->
    <div v-if="selectedInvoice" class="modal-backdrop" @click.self="selectedInvoice = null">
      <div class="invoice-modal-card">
        <div class="modal-header-bar">
          <div class="brand-modal-logo">
            <img src="@/assets/Gittax/logo1.png" alt="Logo" class="brand-logo" />
            <div>
              <h2 class="modal-title">Fawaz Platform Tax Invoice</h2>
              <span class="modal-subtitle">Simplified VAT Invoice • فاتورة ضريبية مبسطة</span>
            </div>
          </div>
          <div class="modal-tools">
            <button class="print-btn" @click="printInvoice">🖨️ Print / Save PDF</button>
            <button class="close-modal-btn" @click="selectedInvoice = null">✕</button>
          </div>
        </div>

        <div class="invoice-printable-area" id="print-area">
          <div class="inv-meta-grid">
            <div>
              <span class="meta-label">Invoice Number:</span>
              <strong class="meta-val">{{ selectedInvoice.number }}</strong>
              <div class="meta-label" style="margin-top:8px">Issue Date:</div>
              <span class="meta-val">{{ selectedInvoice.date }}</span>
            </div>
            <div>
              <span class="meta-label">Tax / VAT ID:</span>
              <strong class="meta-val">300987654300003</strong>
              <div class="meta-label" style="margin-top:8px">Commercial Reg (CR):</div>
              <span class="meta-val">1010892341</span>
            </div>
            <div>
              <span class="meta-label">Billed To:</span>
              <strong class="meta-val">{{ selectedInvoice.client }}</strong>
              <div class="meta-sub">{{ selectedInvoice.email }}</div>
            </div>
          </div>

          <table class="inv-items-table">
            <thead>
              <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>VAT (15%)</th>
                <th>Total (SAR)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ selectedInvoice.service }}</td>
                <td>1</td>
                <td>SAR {{ (selectedInvoice.amount / 1.15).toFixed(2) }}</td>
                <td>SAR {{ (selectedInvoice.amount - (selectedInvoice.amount / 1.15)).toFixed(2) }}</td>
                <td>SAR {{ selectedInvoice.amount.toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="inv-totals-box">
            <div class="total-line">
              <span>Subtotal (Excl. VAT):</span>
              <span>SAR {{ (selectedInvoice.amount / 1.15).toFixed(2) }}</span>
            </div>
            <div class="total-line">
              <span>VAT (15%):</span>
              <span>SAR {{ (selectedInvoice.amount - (selectedInvoice.amount / 1.15)).toFixed(2) }}</span>
            </div>
            <div class="total-line grand">
              <span>Total Amount Due:</span>
              <span>SAR {{ selectedInvoice.amount.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Create New Invoice -->
    <div v-if="showCreateModal" class="modal-backdrop" @click.self="showCreateModal = false">
      <div class="create-modal-card">
        <div class="modal-header-bar">
          <h2 class="modal-title">➕ Create New Tax Invoice</h2>
          <button class="close-modal-btn" @click="showCreateModal = false">✕</button>
        </div>

        <form @submit.prevent="createNewInvoice" class="create-form">
          <div class="form-grid">
            <div class="form-group">
              <label>Client Name *</label>
              <input v-model="newInv.client" class="form-ctrl" placeholder="e.g. Al-Noor Spa Lounge" required />
            </div>
            <div class="form-group">
              <label>Client Email *</label>
              <input v-model="newInv.email" type="email" class="form-ctrl" placeholder="billing@alnoor.sa" required />
            </div>
            <div class="form-group">
              <label>Service Description *</label>
              <input v-model="newInv.service" class="form-ctrl" placeholder="e.g. VIP Wellness Package" required />
            </div>
            <div class="form-group">
              <label>Total Amount (SAR) *</label>
              <input v-model.number="newInv.amount" type="number" step="0.01" class="form-ctrl" placeholder="1500" required />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="newInv.status" class="form-ctrl">
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
              </select>
            </div>
            <div class="form-group">
              <label>Due Date</label>
              <input v-model="newInv.dueDate" type="date" class="form-ctrl" required />
            </div>
          </div>

          <div class="modal-footer-btns">
            <button type="button" class="btn-cancel" @click="showCreateModal = false">Cancel</button>
            <button type="submit" class="btn-submit">Generate Invoice</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'InvoicesPage',
  data() {
    return {
      displayName: 'Fawaz Alharbi',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      isArabic: false,
      activeTab: 'all',
      searchQuery: '',
      selectedInvoice: null,
      showCreateModal: false,
      tabs: [
        { key: 'all',     label: 'All Invoices' },
        { key: 'paid',    label: 'Paid' },
        { key: 'pending', label: 'Pending' },
        { key: 'overdue', label: 'Overdue' }
      ],
      newInv: {
        client: '',
        email: '',
        service: '',
        amount: 1200,
        status: 'pending',
        dueDate: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]
      },
      invoices: [
        { id: 1, number: 'INV-2026-001', client: 'Al-Noor Spa & Wellness', email: 'billing@alnoor.sa', service: 'Monthly Enterprise Gateway Subscription', date: '2026-08-01', dueDate: '2026-08-15', amount: 4850, status: 'paid' },
        { id: 2, number: 'INV-2026-002', client: 'Elite Barbershop Riyadh', email: 'owner@elitebarber.sa', service: 'VIP Booking Platform Package', date: '2026-08-03', dueDate: '2026-08-17', amount: 3200, status: 'paid' },
        { id: 3, number: 'INV-2026-003', client: 'Gourmet Dining Lounge', email: 'finance@gourmetlounge.sa', service: 'Premium Merchant Integration & CRM', date: '2026-08-05', dueDate: '2026-08-19', amount: 6500, status: 'pending' },
        { id: 4, number: 'INV-2026-004', client: 'Oasis Fitness Center', email: 'admin@oasisfit.sa', service: 'Quarterly Booking Commission Settlement', date: '2026-08-08', dueDate: '2026-08-22', amount: 8900, status: 'paid' },
        { id: 5, number: 'INV-2026-005', client: 'Royal Dental Clinic', email: 'accounting@royaldental.sa', service: 'Online Appointment Gateway Licensing', date: '2026-07-20', dueDate: '2026-08-04', amount: 5400, status: 'overdue' },
        { id: 6, number: 'INV-2026-006', client: 'Lumiere Beauty Studio', email: 'lumiere@beautystudio.sa', service: 'Multi-Branch Booking Sync & Ads', date: '2026-08-10', dueDate: '2026-08-24', amount: 2750, status: 'pending' },
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
    totalInvoiced() {
      return this.invoices.reduce((acc, inv) => acc + inv.amount, 0);
    },
    totalPaid() {
      return this.invoices.filter(i => i.status === 'paid').reduce((acc, inv) => acc + inv.amount, 0);
    },
    totalPending() {
      return this.invoices.filter(i => i.status !== 'paid').reduce((acc, inv) => acc + inv.amount, 0);
    },
    paidPercentage() {
      if (this.totalInvoiced === 0) return 0;
      return Math.round((this.totalPaid / this.totalInvoiced) * 100);
    },
    filteredInvoices() {
      return this.invoices.filter(inv => {
        const matchesTab = this.activeTab === 'all' || inv.status === this.activeTab;
        const q = this.searchQuery.toLowerCase();
        const matchesSearch = !q ||
          inv.number.toLowerCase().includes(q) ||
          inv.client.toLowerCase().includes(q) ||
          inv.service.toLowerCase().includes(q);
        return matchesTab && matchesSearch;
      });
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

    const saved = localStorage.getItem('fawaz_invoices');
    if (saved) {
      try {
        this.invoices = JSON.parse(saved);
      } catch (e) {
        console.warn('Failed to parse invoices', e);
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

    getTabCount(key) {
      if (key === 'all') return this.invoices.length;
      return this.invoices.filter(i => i.status === key).length;
    },

    viewInvoice(inv) {
      this.selectedInvoice = inv;
    },

    printInvoice() {
      window.print();
    },

    markAsPaid(inv) {
      inv.status = 'paid';
      this.saveToStorage();
    },

    deleteInvoice(id) {
      if (confirm('Are you sure you want to delete this invoice?')) {
        this.invoices = this.invoices.filter(i => i.id !== id);
        this.saveToStorage();
      }
    },

    createNewInvoice() {
      const newId = Date.now();
      const num = `INV-2026-00${this.invoices.length + 1}`;
      this.invoices.unshift({
        id: newId,
        number: num,
        client: this.newInv.client,
        email: this.newInv.email,
        service: this.newInv.service,
        date: new Date().toISOString().split('T')[0],
        dueDate: this.newInv.dueDate,
        amount: Number(this.newInv.amount),
        status: this.newInv.status
      });

      this.saveToStorage();
      this.showCreateModal = false;
      this.newInv = {
        client: '',
        email: '',
        service: '',
        amount: 1200,
        status: 'pending',
        dueDate: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]
      };
    },

    saveToStorage() {
      localStorage.setItem('fawaz_invoices', JSON.stringify(this.invoices));
    },

    exportCSV() {
      let csv = 'Invoice Number,Client,Email,Service,Date,Due Date,Amount,Status\n';
      this.invoices.forEach(i => {
        csv += `"${i.number}","${i.client}","${i.email}","${i.service}","${i.date}","${i.dueDate}",${i.amount},"${i.status}"\n`;
      });
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `Invoices_Export_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
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
.invoices-page-root {
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.invoices-page-root.dark {
  background: #0f172a;
  color: #f8fafc;
}
.invoices-page-root.rtl {
  direction: rtl;
}

/* Navbar */
.invoices-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.dark .invoices-navbar {
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
.invoices-main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px 60px;
}

.invoices-header-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.invoices-main-title {
  font-size: 1.75rem;
  font-weight: 900;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}
.invoices-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
}
.dark .invoices-subtitle {
  color: #94a3b8;
}

.header-action-group {
  display: flex;
  gap: 10px;
}

.create-invoice-btn {
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
.create-invoice-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
}

.export-data-btn {
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
.dark .export-data-btn {
  background: #181824;
  border-color: #475569;
  color: #e2e8f0;
}
.export-data-btn:hover {
  background: #f1f5f9;
}

/* KPI Cards */
.invoices-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}
.inv-kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
.dark .inv-kpi-card {
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
.kpi-icon-wrap.orange { background: rgba(245, 158, 11, 0.15); }
.kpi-icon-wrap.purple { background: rgba(139, 92, 246, 0.15); }

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

/* Filter Bar */
.invoices-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  gap: 14px;
}

.status-tabs-wrap {
  display: flex;
  gap: 8px;
}
.status-tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.dark .status-tab-btn {
  background: #181824;
  border-color: #475569;
  color: #cbd5e1;
}
.status-tab-btn.active {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
}

.tab-count-badge {
  background: rgba(0, 0, 0, 0.08);
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 8px;
}
.status-tab-btn.active .tab-count-badge {
  background: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

.search-wrap {
  flex: 1;
  max-width: 380px;
}
.search-input-box {
  width: 100%;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.85rem;
  outline: none;
}
.dark .search-input-box {
  background: #1e1e2e;
  border-color: #475569;
}
.search-input-box:focus {
  border-color: #0284c7;
}

/* Table Card */
.invoices-table-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.dark .invoices-table-card {
  background: #181824;
  border-color: #2d3748;
}

.invoices-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}
.rtl .invoices-table {
  text-align: right;
}

.invoices-table th {
  background: #f8fafc;
  padding: 14px 18px;
  font-size: 0.75rem;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
}
.dark .invoices-table th {
  background: #1e1e2e;
  color: #94a3b8;
  border-bottom-color: #2d3748;
}

.invoices-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.dark .invoices-table td {
  border-bottom-color: #282a36;
}

.invoice-num {
  font-family: monospace;
  font-weight: 800;
  color: #0284c7;
}

.client-cell {
  display: flex;
  flex-direction: column;
}
.client-name {
  font-weight: 700;
}
.client-sub {
  font-size: 0.72rem;
  color: #94a3b8;
}

.service-tag {
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}
.dark .service-tag {
  background: #282a36;
}

.amount-text {
  font-size: 0.95rem;
  font-weight: 800;
}

.status-badge {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-block;
}
.status-badge.paid    { background: #dcfce7; color: #166534; }
.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.overdue { background: #fee2e2; color: #991b1b; }

.table-actions {
  display: flex;
  justify-content: center;
  gap: 6px;
}
.tbl-action-btn {
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
}
.tbl-action-btn.view {
  background: #0284c7;
  color: #ffffff;
}
.tbl-action-btn.pay {
  background: #10b981;
  color: #ffffff;
}
.tbl-action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.empty-state-cell {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
.empty-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 8px;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
}

.invoice-modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 720px;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  max-height: 90vh;
  overflow-y: auto;
}
.dark .invoice-modal-card {
  background: #181824;
  color: #f8fafc;
}

.create-modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 580px;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
}
.dark .create-modal-card {
  background: #181824;
  color: #f8fafc;
}

.modal-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid #e2e8f0;
}
.dark .modal-header-bar {
  border-bottom-color: #2d3748;
}

.brand-modal-logo {
  display: flex;
  align-items: center;
  gap: 12px;
}
.modal-title {
  font-size: 1.15rem;
  font-weight: 800;
  margin: 0;
}
.modal-subtitle {
  font-size: 0.75rem;
  color: #64748b;
}

.modal-tools {
  display: flex;
  align-items: center;
  gap: 8px;
}
.print-btn {
  background: #0284c7;
  color: #ffffff;
  border: none;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}
.close-modal-btn {
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-size: 0.9rem;
}
.dark .close-modal-btn {
  background: #282a36;
  color: #ffffff;
}

.inv-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 0.85rem;
}
.dark .inv-meta-grid {
  background: #1e1e2e;
}
.meta-label {
  font-size: 0.72rem;
  color: #64748b;
  display: block;
}
.meta-val {
  font-size: 0.85rem;
}
.meta-sub {
  font-size: 0.75rem;
  color: #94a3b8;
}

.inv-items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 0.85rem;
}
.inv-items-table th {
  background: #f1f5f9;
  padding: 10px 14px;
  text-align: left;
}
.rtl .inv-items-table th {
  text-align: right;
}
.dark .inv-items-table th {
  background: #282a36;
}
.inv-items-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #e2e8f0;
}
.dark .inv-items-table td {
  border-bottom-color: #2d3748;
}

.inv-totals-box {
  margin-left: auto;
  width: 260px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.85rem;
}
.rtl .inv-totals-box {
  margin-left: 0;
  margin-right: auto;
}
.total-line {
  display: flex;
  justify-content: space-between;
}
.total-line.grand {
  font-size: 1rem;
  font-weight: 800;
  color: #0284c7;
  border-top: 2px solid #e2e8f0;
  padding-top: 6px;
  margin-top: 4px;
}

/* Create Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group label {
  font-size: 0.8rem;
  font-weight: 700;
}
.form-ctrl {
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.85rem;
  outline: none;
}
.dark .form-ctrl {
  background: #1e1e2e;
  border-color: #475569;
}
.form-ctrl:focus {
  border-color: #0284c7;
}

.modal-footer-btns {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 24px;
}
.btn-cancel {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
}
.dark .btn-cancel {
  background: #282a36;
  border-color: #475569;
  color: #ffffff;
}
.btn-submit {
  background: #0284c7;
  color: #ffffff;
  border: none;
  padding: 10px 22px;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
}
</style>
