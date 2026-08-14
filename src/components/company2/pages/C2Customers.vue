<template>
  <Company2Layout pageTitle="Customers Management">
    <div class="c2-page">
      <!-- Top Metrics / KPI Cards -->
      <div class="c2-stats-row">
        <div class="c2-stat-card" v-for="(kpi, idx) in kpis" :key="idx">
          <div class="stat-icon-wrap" :style="{ backgroundColor: kpi.bg }">
            <span>{{ kpi.icon }}</span>
          </div>
          <div class="stat-info">
            <span class="c2-stat-label">{{ kpi.label }}</span>
            <span class="c2-stat-value">{{ kpi.value }}</span>
            <span class="stat-trend">{{ kpi.sub }}</span>
          </div>
        </div>
      </div>

      <!-- Controls & Filter Bar -->
      <div class="c2-card c2-mb-4">
        <div class="c2-filter-bar">
          <div class="c2-search-box">
            <span class="search-icon">🔍</span>
            <input
              type="text"
              v-model="searchQuery"
              class="c2-input"
              placeholder="Search customer name, email or phone..."
            />
          </div>

          <div class="filter-group">
            <div class="status-tabs">
              <button
                v-for="st in ['All', 'VIP', 'Active', 'Inactive']"
                :key="st"
                :class="['tab-pill', { active: selectedStatus === st }]"
                @click="selectedStatus = st"
              >
                {{ st }}
              </button>
            </div>

            <button class="c2-btn c2-btn-secondary" @click="exportCSV" title="Export CSV">
              📥 Export CSV
            </button>
            <button class="c2-btn c2-btn-primary" @click="openCreateModal">
              ➕ Add Customer
            </button>
          </div>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="c2-card">
        <div class="c2-card-header">
          <h3 class="c2-card-title">👥 Customer Directory ({{ filteredCustomers.length }})</h3>
          <span class="table-subtitle">Comprehensive client profiles, activity and lifetime booking values</span>
        </div>

        <div class="c2-table-responsive">
          <table class="c2-table">
            <thead>
              <tr>
                <th>Customer</th>
                <th>Contact Info</th>
                <th>Total Bookings</th>
                <th>Total Spent</th>
                <th>Status</th>
                <th>Member Since</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cust in filteredCustomers" :key="cust.id">
                <td>
                  <div class="customer-profile-cell">
                    <img :src="cust.avatar || defaultAvatar" alt="Avatar" class="cust-avatar" />
                    <div>
                      <strong class="cust-name">{{ cust.name }}</strong>
                      <span class="cust-id">ID #CUST-{{ 1000 + cust.id }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="contact-meta">
                    <span>✉️ {{ cust.email }}</span>
                    <span class="phone-text">📞 {{ cust.phone }}</span>
                  </div>
                </td>
                <td>
                  <span class="bookings-badge">🎟️ {{ cust.total_bookings }} Bookings</span>
                </td>
                <td>
                  <strong class="price-val">SAR {{ (cust.total_spent || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>
                </td>
                <td>
                  <span :class="['cust-status-badge', cust.status.toLowerCase()]">
                    {{ cust.status === 'VIP' ? '👑 VIP' : cust.status }}
                  </span>
                </td>
                <td>
                  <span class="date-cell">{{ formatDate(cust.member_since) }}</span>
                </td>
                <td>
                  <div class="c2-btn-group">
                    <button class="action-icon-btn view" @click="viewCustomer(cust)" title="View Profile">
                      👁️
                    </button>
                    <button class="action-icon-btn edit" @click="editCustomer(cust)" title="Edit Customer">
                      ✏️
                    </button>
                    <button class="action-icon-btn toggle" @click="toggleCustomerStatus(cust)" title="Toggle Status">
                      🔄
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredCustomers.length === 0">
                <td colspan="7" class="c2-text-center c2-py-4">
                  <div class="empty-state-box">
                    <span>👥</span>
                    <p>No customers found matching "{{ searchQuery }}"</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add/Edit Customer Modal -->
      <div v-if="isModalOpen" class="c2-modal-backdrop" @click.self="isModalOpen = false">
        <div class="c2-modal">
          <div class="c2-modal-header">
            <h3>{{ editingCustomer ? '✏️ Edit Customer Profile' : '➕ Register New Customer' }}</h3>
            <button class="c2-modal-close" @click="isModalOpen = false">✕</button>
          </div>
          <form @submit.prevent="saveCustomer" class="modal-form">
            <div class="form-group">
              <label>Full Name *</label>
              <input type="text" v-model="form.name" required class="c2-input" placeholder="e.g. Faisal Al-Otaibi" />
            </div>
            <div class="form-group">
              <label>Email Address *</label>
              <input type="email" v-model="form.email" required class="c2-input" placeholder="e.g. faisal@example.com" />
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" v-model="form.phone" class="c2-input" placeholder="+966 5X XXX XXXX" />
            </div>
            <div class="form-group">
              <label>Membership Tier / Status</label>
              <select v-model="form.status" class="c2-select">
                <option value="Active">Active Customer</option>
                <option value="VIP">VIP Gold Tier</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="c2-modal-footer">
              <button type="button" class="c2-btn c2-btn-secondary" @click="isModalOpen = false">Cancel</button>
              <button type="submit" class="c2-btn c2-btn-primary">Save Profile</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Customer Detail & History Modal -->
      <div v-if="selectedCustModal" class="c2-modal-backdrop" @click.self="selectedCustModal = null">
        <div class="c2-modal large">
          <div class="c2-modal-header">
            <div class="modal-user-head">
              <img :src="selectedCustModal.avatar || defaultAvatar" alt="" class="modal-avatar" />
              <div>
                <h3>{{ selectedCustModal.name }}</h3>
                <span class="modal-sub">ID: #CUST-{{ 1000 + selectedCustModal.id }} • {{ selectedCustModal.status }}</span>
              </div>
            </div>
            <button class="c2-modal-close" @click="selectedCustModal = null">✕</button>
          </div>
          <div class="detail-body">
            <div class="detail-kpis">
              <div class="detail-kpi-card">
                <span>Total Bookings</span>
                <strong>🎟️ {{ selectedCustModal.total_bookings }}</strong>
              </div>
              <div class="detail-kpi-card">
                <span>Total Spending</span>
                <strong>SAR {{ (selectedCustModal.total_spent || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>
              </div>
              <div class="detail-kpi-card">
                <span>Member Since</span>
                <strong>📅 {{ formatDate(selectedCustModal.member_since) }}</strong>
              </div>
            </div>

            <h4 class="history-title">Recent Appointments & Bookings</h4>
            <div class="history-list">
              <div class="history-item" v-for="i in 3" :key="i">
                <div class="hist-icon">📅</div>
                <div class="hist-info">
                  <strong>Service Booking #BK-{{ 1040 + i * 7 }}</strong>
                  <span>Prime Salon & Spa • Confirmed</span>
                </div>
                <span class="hist-price">SAR {{ 150 + i * 45 }}.00</span>
              </div>
            </div>
          </div>
          <div class="c2-modal-footer">
            <button class="c2-btn c2-btn-primary" @click="selectedCustModal = null">Close Details</button>
          </div>
        </div>
      </div>

    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { customerApi } from '@/services/api';

export default {
  name: 'C2Customers',
  components: { Company2Layout },
  data() {
    return {
      searchQuery: '',
      selectedStatus: 'All',
      isModalOpen: false,
      editingCustomer: null,
      selectedCustModal: null,
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      form: {
        name: '',
        email: '',
        phone: '',
        status: 'Active'
      },
      kpis: [
        { label: 'Total Clients', value: '48', sub: '+14% new this month', icon: '👥', bg: 'rgba(0, 170, 255, 0.15)' },
        { label: 'VIP Members', value: '12', sub: 'Top revenue contributors', icon: '👑', bg: 'rgba(245, 158, 11, 0.15)' },
        { label: 'Total Appointments', value: '185', sub: 'Across 10 merchants', icon: '🎟️', bg: 'rgba(147, 51, 234, 0.15)' },
        { label: 'Client Lifetime Spend', value: 'SAR 94,850', sub: 'Avg SAR 1,976 / client', icon: '💰', bg: 'rgba(16, 185, 129, 0.15)' },
      ],
      customers: [
        { id: 1, name: 'Fawaz Al-Harbi', email: 'fawaz@example.com', phone: '+966 501234567', total_bookings: 8, total_spent: 4250, status: 'VIP', member_since: '2025-01-15', avatar: 'https://i.pravatar.cc/150?u=Fawaz' },
        { id: 2, name: 'Sarah Al-Mansoor', email: 'sarah.m@example.com', phone: '+966 548882211', total_bookings: 5, total_spent: 2100, status: 'VIP', member_since: '2025-02-10', avatar: 'https://i.pravatar.cc/150?u=Sarah' },
        { id: 3, name: 'Khaled Bin Rashid', email: 'khaled.r@example.com', phone: '+966 567778899', total_bookings: 3, total_spent: 850, status: 'Active', member_since: '2025-03-01', avatar: 'https://i.pravatar.cc/150?u=Khaled' },
        { id: 4, name: 'Noura Al-Ghamdi', email: 'noura@example.com', phone: '+966 509991122', total_bookings: 4, total_spent: 1450, status: 'Active', member_since: '2025-03-22', avatar: 'https://i.pravatar.cc/150?u=Noura' },
        { id: 5, name: 'Omar Al-Zahrani', email: 'omar.z@example.com', phone: '+966 551122334', total_bookings: 6, total_spent: 3100, status: 'VIP', member_since: '2025-04-05', avatar: 'https://i.pravatar.cc/150?u=Omar' },
        { id: 6, name: 'Reem Al-Shehri', email: 'reem.sh@example.com', phone: '+966 534455667', total_bookings: 1, total_spent: 180, status: 'Inactive', member_since: '2025-05-12', avatar: 'https://i.pravatar.cc/150?u=Reem' },
        { id: 7, name: 'Abdullah Al-Kindi', email: 'abdullah.k@example.com', phone: '+966 592233445', total_bookings: 2, total_spent: 560, status: 'Active', member_since: '2025-05-28', avatar: 'https://i.pravatar.cc/150?u=Abdullah' },
      ]
    };
  },
  computed: {
    filteredCustomers() {
      return this.customers.filter(c => {
        const matchesQuery = c.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             c.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             c.phone.includes(this.searchQuery);
        const matchesStatus = this.selectedStatus === 'All' || c.status.toLowerCase() === this.selectedStatus.toLowerCase();
        return matchesQuery && matchesStatus;
      });
    }
  },
  async mounted() {
    await this.fetchCustomers();
  },
  methods: {
    async fetchCustomers() {
      try {
        const res = await customerApi.list();
        if (res && Array.isArray(res.data) && res.data.length > 0) {
          this.customers = res.data;
          this.kpis[0].value = `${res.data.length}`;
          const vipCount = res.data.filter(x => x.status === 'VIP').length;
          this.kpis[1].value = `${vipCount}`;
          const totalRev = res.data.reduce((acc, c) => acc + (Number(c.total_spent) || 0), 0);
          if (totalRev > 0) {
            this.kpis[3].value = `SAR ${totalRev.toLocaleString('en-US', { minimumFractionDigits: 0 })}`;
          }
        }
      } catch (e) {
        console.warn('Using seeded customer dataset', e);
      }
    },
    openCreateModal() {
      this.editingCustomer = null;
      this.form = { name: '', email: '', phone: '', status: 'Active' };
      this.isModalOpen = true;
    },
    editCustomer(cust) {
      this.editingCustomer = cust;
      this.form = { ...cust };
      this.isModalOpen = true;
    },
    viewCustomer(cust) {
      this.selectedCustModal = cust;
    },
    toggleCustomerStatus(cust) {
      cust.status = cust.status === 'Active' ? 'VIP' : cust.status === 'VIP' ? 'Inactive' : 'Active';
    },
    async saveCustomer() {
      if (this.editingCustomer) {
        Object.assign(this.editingCustomer, this.form);
      } else {
        const newCust = {
          id: this.customers.length + 1,
          name: this.form.name,
          email: this.form.email,
          phone: this.form.phone || '+966 500000000',
          total_bookings: 0,
          total_spent: 0,
          status: this.form.status,
          member_since: new Date().toISOString().substring(0, 10),
          avatar: `https://i.pravatar.cc/150?u=${encodeURIComponent(this.form.name)}`
        };
        this.customers.unshift(newCust);
        customerApi.create(this.form).catch(() => null);
      }
      this.isModalOpen = false;
    },
    exportCSV() {
      const headers = ['ID', 'Name', 'Email', 'Phone', 'Total Bookings', 'Total Spent (SAR)', 'Status', 'Member Since'];
      const rows = this.filteredCustomers.map(c => [
        c.id,
        `"${c.name}"`,
        c.email,
        `"${c.phone}"`,
        c.total_bookings,
        c.total_spent,
        c.status,
        c.member_since
      ]);
      const csv = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
      const uri = encodeURI(csv);
      const link = document.createElement('a');
      link.href = uri;
      link.download = `customers_report_${new Date().toISOString().substring(0, 10)}.csv`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    formatDate(d) {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
  }
};
</script>

<style scoped>
.c2-stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.c2-stat-card {
  background: var(--c2-card-bg);
  border-radius: var(--c2-radius);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid var(--c2-border);
  box-shadow: var(--c2-shadow);
  transition: transform 0.2s;
}
.c2-stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.c2-stat-label {
  font-size: 0.8rem;
  color: var(--c2-text-muted);
  font-weight: 600;
}

.c2-stat-value {
  font-size: 1.35rem;
  font-weight: 800;
  line-height: 1.2;
  margin: 2px 0;
}

.stat-trend {
  font-size: 0.72rem;
  color: var(--c2-success);
  font-weight: 600;
}

.c2-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.c2-search-box {
  position: relative;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.9rem;
  opacity: 0.6;
}

.c2-search-box .c2-input {
  padding-left: 36px;
  width: 100%;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.status-tabs {
  display: flex;
  background: rgba(0, 0, 0, 0.04);
  padding: 4px;
  border-radius: 10px;
  gap: 4px;
}
.dark .status-tabs {
  background: rgba(255, 255, 255, 0.05);
}

.tab-pill {
  background: none;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  color: var(--c2-text-muted);
  transition: all 0.2s;
}

.tab-pill.active {
  background: var(--c2-accent);
  color: #fff;
}

.customer-profile-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cust-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--c2-border);
}

.cust-name {
  font-size: 0.9rem;
  display: block;
}

.cust-id {
  font-size: 0.72rem;
  color: var(--c2-text-muted);
}

.contact-meta {
  display: flex;
  flex-direction: column;
  font-size: 0.8rem;
  gap: 2px;
}

.phone-text {
  color: var(--c2-text-muted);
  font-size: 0.75rem;
}

.bookings-badge {
  background: rgba(147, 51, 234, 0.1);
  color: #8b5cf6;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 12px;
}

.price-val {
  color: var(--c2-success);
}

.cust-status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
}
.cust-status-badge.vip      { background: #fef3c7; color: #b45309; }
.cust-status-badge.active   { background: #dcfce7; color: #15803d; }
.cust-status-badge.inactive { background: #fee2e2; color: #b91c1c; }

.dark .cust-status-badge.vip      { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.dark .cust-status-badge.active   { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
.dark .cust-status-badge.inactive { background: rgba(239, 68, 68, 0.2); color: #f87171; }

.action-icon-btn {
  background: rgba(0, 0, 0, 0.04);
  border: 1px solid var(--c2-border);
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  transition: transform 0.15s;
}
.dark .action-icon-btn {
  background: rgba(255, 255, 255, 0.06);
}
.action-icon-btn:hover {
  transform: scale(1.15);
}

/* Modals */
.c2-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.c2-modal {
  background: var(--c2-card-bg);
  border-radius: var(--c2-radius);
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
  border: 1px solid var(--c2-border);
  overflow: hidden;
}

.c2-modal.large {
  max-width: 680px;
}

.c2-modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--c2-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-user-head {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 2px solid var(--c2-accent);
}

.modal-sub {
  font-size: 0.75rem;
  color: var(--c2-text-muted);
}

.c2-modal-close {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: var(--c2-text-muted);
}

.modal-form {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-body {
  padding: 24px;
}

.detail-kpis {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 24px;
}

.detail-kpi-card {
  background: rgba(0, 170, 255, 0.06);
  border: 1px solid var(--c2-border);
  padding: 14px;
  border-radius: var(--c2-radius-sm);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-kpi-card span {
  font-size: 0.75rem;
  color: var(--c2-text-muted);
}

.history-title {
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 12px;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.history-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border: 1px solid var(--c2-border);
  border-radius: var(--c2-radius-sm);
  background: rgba(0, 0, 0, 0.02);
}
.dark .history-item {
  background: rgba(255, 255, 255, 0.02);
}

.hist-icon {
  font-size: 1.2rem;
}

.hist-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  font-size: 0.8rem;
}

.hist-price {
  font-weight: 700;
  color: var(--c2-accent);
  font-size: 0.85rem;
}

.c2-modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--c2-border);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
