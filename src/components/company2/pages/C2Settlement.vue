<template>
  <Company2Layout page-title="Settlement">
    <!-- Summary Cards -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
      <div class="c2-stat-card" style="border-left-color:#27ae60"><div class="c2-stat-label">Available Balance</div><div class="c2-stat-value">SAR {{ balance.toLocaleString() }}</div><div class="c2-stat-sub">Ready to payout</div></div>
      <div class="c2-stat-card" style="border-left-color:#f39c12"><div class="c2-stat-label">Pending Payout</div><div class="c2-stat-value">SAR {{ pending.toLocaleString() }}</div><div class="c2-stat-sub">Processing 2-3 days</div></div>
      <div class="c2-stat-card" style="border-left-color:#00aaff"><div class="c2-stat-label">Total Paid</div><div class="c2-stat-value">SAR {{ totalPaid.toLocaleString() }}</div><div class="c2-stat-sub">All time</div></div>
      <div class="c2-stat-card" style="border-left-color:#9b59b6"><div class="c2-stat-label">Transactions</div><div class="c2-stat-value">{{ settlements.length }}</div><div class="c2-stat-sub">Total records</div></div>
    </div>

    <div class="c2-section-header">
      <h2 class="c2-section-title">Settlement History</h2>
      <div style="display:flex;gap:10px">
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Status</option><option>Paid</option><option>Pending</option><option>Processing</option>
        </select>
        <button class="c2-btn c2-btn-warning">📥 Export</button>
      </div>
    </div>

    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr><th>ID</th><th>Merchant</th><th>Date</th><th>Amount (SAR)</th><th>Method</th><th>Status</th></tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredSettlements" :key="s.id">
              <td class="mono">#{{ s.id }}</td>
              <td><strong>{{ s.merchant }}</strong></td>
              <td>{{ s.date }}</td>
              <td><strong>{{ s.amount.toLocaleString() }}</strong></td>
              <td>{{ s.method }}</td>
              <td><span :class="['c2-badge', 'c2-badge-' + s.status.toLowerCase()]">{{ s.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Settlement',
  components: { Company2Layout },
  data() {
    return {
      statusFilter: '',
      settlements: [
        { id: 'STL-301', merchant: 'Beta Eats', date: '2025-04-01', amount: 8450, method: 'Bank Transfer', status: 'Paid' },
        { id: 'STL-302', merchant: 'Acme Store', date: '2025-04-01', amount: 3200, method: 'Bank Transfer', status: 'Paid' },
        { id: 'STL-303', merchant: 'Zeta Fashion', date: '2025-04-02', amount: 5600, method: 'SADAD', status: 'Processing' },
        { id: 'STL-304', merchant: 'Gamma Tech', date: '2025-04-03', amount: 2100, method: 'Bank Transfer', status: 'Pending' },
        { id: 'STL-305', merchant: 'Epsilon Services', date: '2025-04-03', amount: 4750, method: 'SADAD', status: 'Pending' },
        { id: 'STL-306', merchant: 'Delta Health', date: '2025-03-28', amount: 6300, method: 'Bank Transfer', status: 'Paid' },
        { id: 'STL-307', merchant: 'Beta Eats', date: '2025-03-25', amount: 7100, method: 'Bank Transfer', status: 'Paid' }
      ]
    };
  },
  computed: {
    filteredSettlements() {
      if (!this.statusFilter) return this.settlements;
      return this.settlements.filter(s => s.status === this.statusFilter);
    },
    balance() { return 14650; },
    pending() { return this.settlements.filter(s => s.status === 'Pending' || s.status === 'Processing').reduce((a, s) => a + s.amount, 0); },
    totalPaid() { return this.settlements.filter(s => s.status === 'Paid').reduce((a, s) => a + s.amount, 0); }
  }
};
</script>
<style scoped>.mono { font-family: monospace; font-size: 0.82rem; }</style>
