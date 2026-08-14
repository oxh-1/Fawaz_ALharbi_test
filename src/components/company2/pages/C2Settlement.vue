<template>
  <Company2Layout page-title="Settlements & Payouts">
    <!-- Summary Cards -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));margin-bottom:24px">
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Available Balance</div>
        <div class="c2-stat-value">SAR {{ balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
        <div class="c2-stat-sub">Ready for settlement</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Pending Payouts</div>
        <div class="c2-stat-value">SAR {{ pendingTotal.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
        <div class="c2-stat-sub">{{ pendingCount }} pending batch(es)</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#00aaff">
        <div class="c2-stat-label">Total Settled</div>
        <div class="c2-stat-value">SAR {{ totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
        <div class="c2-stat-sub">Lifetime volume</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#9b59b6">
        <div class="c2-stat-label">Transactions</div>
        <div class="c2-stat-value">{{ settlements.length }}</div>
        <div class="c2-stat-sub">Ledger entries</div>
      </div>
    </div>

    <!-- Section Header & Actions -->
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Settlement Ledger & Invoices</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Automated merchant payouts, IBAN bank transfers, and settlement reconciliations.
        </p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="c2-btn c2-btn-ghost" @click="exportCSV">📥 Export CSV</button>
        <button class="c2-btn c2-btn-primary" @click="openPayoutModal">+ Process Payout</button>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search merchant, settlement ID..." />
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Statuses</option>
          <option value="completed">Completed / Paid</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
        </select>
        <select v-model="methodFilter" class="c2-select">
          <option value="">All Methods</option>
          <option value="Bank Transfer">Bank Transfer (SARIE)</option>
          <option value="Credit Card">Credit Card / Mada</option>
          <option value="STC Pay">STC Pay</option>
          <option value="Apple Pay">Apple Pay</option>
        </select>
      </div>
      <span style="font-size:0.85rem;color:var(--c2-text-muted)">{{ filteredSettlements.length }} records</span>
    </div>

    <!-- Table -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr>
              <th>Settlement Ref</th>
              <th>Merchant</th>
              <th>Date</th>
              <th>Amount (SAR)</th>
              <th>Payment Method</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredSettlements" :key="s.id">
              <td class="mono">
                <strong>{{ s.settlement_id || ('#SET-' + s.id) }}</strong>
              </td>
              <td><strong>{{ s.merchant }}</strong></td>
              <td>{{ s.date }}</td>
              <td>
                <strong style="color:var(--c2-text);font-size:0.95rem">
                  SAR {{ Number(s.amount).toFixed(2) }}
                </strong>
              </td>
              <td>
                <span class="c2-badge" style="background:var(--c2-bg);color:var(--c2-text)">
                  💳 {{ s.method || 'Bank Transfer' }}
                </span>
              </td>
              <td>
                <span :class="['c2-badge', 'c2-badge-' + String(s.status).toLowerCase()]">
                  {{ s.status }}
                </span>
              </td>
              <td>
                <button
                  v-if="String(s.status).toLowerCase() !== 'completed' && String(s.status).toLowerCase() !== 'paid'"
                  class="c2-btn c2-btn-success c2-btn-sm"
                  @click="markCompleted(s)">
                  ✓ Complete
                </button>
                <span v-else style="color:#27ae60;font-size:0.8rem;font-weight:600">✓ Settled</span>
              </td>
            </tr>

            <tr v-if="filteredSettlements.length === 0">
              <td colspan="7" style="text-align:center;padding:40px;color:var(--c2-text-muted)">
                No settlement records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Process Payout Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">Process Merchant Payout</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Select Merchant *</label>
          <select v-model="form.merchant" class="c2-form-select">
            <option v-for="m in merchantsList" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Payout Amount (SAR) *</label>
          <input v-model="form.amount" type="number" step="0.01" class="c2-form-input" placeholder="500.00" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Payment Channel</label>
          <select v-model="form.method" class="c2-form-select">
            <option value="Bank Transfer (SARIE)">Bank Transfer (SARIE Direct)</option>
            <option value="STC Pay">STC Pay Merchant Wallet</option>
            <option value="Mada Transfer">Mada Instant Payout</option>
          </select>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Status</label>
          <select v-model="form.status" class="c2-form-select">
            <option value="Completed">Completed (Transferred)</option>
            <option value="Processing">Processing (Pending bank clearance)</option>
            <option value="Pending">Pending Approval</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="savePayout">Authorize Payout</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { settlementApi, merchantApi } from '@/services/api';

export default {
  name: 'C2Settlement',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      statusFilter: '',
      methodFilter: '',
      showModal: false,
      form: {
        merchant: 'Acme Store',
        amount: 1500,
        method: 'Bank Transfer (SARIE)',
        status: 'Completed'
      },
      settlements: [],
      merchantsList: ['Acme Store', 'Beta Eats', 'Gamma Tech', 'Delta Health', 'Epsilon Services', 'Zeta Fashion']
    };
  },
  async mounted() {
    await this.fetchData();
    this.fetchMerchants();
  },
  computed: {
    filteredSettlements() {
      return this.settlements.filter(s => {
        const q = this.search.toLowerCase();
        const matchQuery = !q || (s.merchant && s.merchant.toLowerCase().includes(q)) ||
                                 (s.settlement_id && s.settlement_id.toLowerCase().includes(q));
        const sStatus = String(s.status).toLowerCase();
        const matchStatus = !this.statusFilter || sStatus === this.statusFilter.toLowerCase() ||
                            (this.statusFilter === 'completed' && sStatus === 'paid');
        const matchMethod = !this.methodFilter || (s.method && s.method.includes(this.methodFilter));
        return matchQuery && matchStatus && matchMethod;
      });
    },
    balance() {
      return 18950.00;
    },
    pendingTotal() {
      return this.settlements
        .filter(s => ['pending', 'processing'].includes(String(s.status).toLowerCase()))
        .reduce((a, s) => a + Number(s.amount || 0), 0);
    },
    pendingCount() {
      return this.settlements.filter(s => ['pending', 'processing'].includes(String(s.status).toLowerCase())).length;
    },
    totalPaid() {
      return this.settlements
        .filter(s => ['completed', 'paid'].includes(String(s.status).toLowerCase()))
        .reduce((a, s) => a + Number(s.amount || 0), 0);
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await settlementApi.list();
        const list = Array.isArray(res) ? res : (res.data || []);
        this.settlements = list.map(s => ({
          ...s,
          amount: Number(s.amount || 0)
        }));
      } catch (e) {
        console.error('Failed to load settlements', e);
      }
    },
    async fetchMerchants() {
      try {
        const res = await merchantApi.list();
        const list = Array.isArray(res) ? res : (res.data || []);
        if (list.length > 0) {
          this.merchantsList = list.map(m => m.name);
          this.form.merchant = this.merchantsList[0];
        }
      } catch (e) {
        console.error(e);
      }
    },
    openPayoutModal() {
      this.form = {
        merchant: this.merchantsList[0] || 'Acme Store',
        amount: 850.00,
        method: 'Bank Transfer (SARIE)',
        status: 'Completed'
      };
      this.showModal = true;
    },
    async savePayout() {
      if (!this.form.amount || Number(this.form.amount) <= 0) {
        alert('Please enter a valid payout amount.');
        return;
      }
      try {
        await settlementApi.create(this.form);
        await this.fetchData();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to process payout', e);
      }
    },
    async markCompleted(s) {
      try {
        await settlementApi.update(s.id, { status: 'Completed' });
        s.status = 'Completed';
      } catch (e) {
        console.error('Failed to complete settlement', e);
      }
    },
    exportCSV() {
      const headers = ['Settlement ID', 'Merchant', 'Date', 'Amount (SAR)', 'Payment Method', 'Status'];
      const rows = this.settlements.map(s => [
        s.settlement_id || ('#SET-' + s.id),
        s.merchant,
        s.date,
        s.amount,
        s.method || 'Bank Transfer',
        s.status
      ]);
      
      const csvContent = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(e => e.map(val => `"${val}"`).join(','))].join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', `settlements_report_${new Date().toISOString().substring(0,10)}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
};
</script>

<style scoped>
.mono {
  font-family: monospace;
  font-size: 0.85rem;
  color: var(--c2-accent);
}
</style>
