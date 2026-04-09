<template>
  <Company2Layout page-title="Reports">
    <!-- Date Filter -->
    <div class="c2-card" style="margin-bottom:20px">
      <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
        <div>
          <label class="c2-form-label">From</label>
          <input type="date" v-model="dateFrom" class="c2-form-input" style="width:160px" />
        </div>
        <div>
          <label class="c2-form-label">To</label>
          <input type="date" v-model="dateTo" class="c2-form-input" style="width:160px" />
        </div>
        <div style="margin-top:auto">
          <button class="c2-btn c2-btn-ghost" @click="resetDates">Reset</button>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px">
          <button class="c2-btn c2-btn-warning">📄 Export PDF</button>
          <button class="c2-btn c2-btn-success">📊 Export Excel</button>
        </div>
      </div>
    </div>

    <!-- Report Cards -->
    <div class="c2-stats-grid" style="margin-bottom:24px">
      <div v-for="r in reportCards" :key="r.label" class="c2-stat-card" :style="{ borderLeftColor: r.color }">
        <div class="c2-stat-label">{{ r.label }}</div>
        <div class="c2-stat-value">{{ r.value }}</div>
        <div class="c2-stat-sub">{{ r.sub }}</div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="c2-grid-2">
      <!-- Revenue by Merchant Bar Chart -->
      <div class="c2-card">
        <h3 class="c2-card-title">💰 Revenue by Merchant (SAR)</h3>
        <div class="c2-chart">
          <div v-for="bar in merchantRevenue" :key="bar.label" class="c2-chart-row">
            <span class="c2-chart-label">{{ bar.label }}</span>
            <div class="c2-chart-bar-wrap">
              <div class="c2-chart-bar" :style="{ width: bar.pct + '%', background: bar.color }">{{ bar.value.toLocaleString() }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bookings by Category Bar Chart -->
      <div class="c2-card">
        <h3 class="c2-card-title">📅 Bookings by Category</h3>
        <div class="c2-chart">
          <div v-for="bar in bookingsByCategory" :key="bar.label" class="c2-chart-row">
            <span class="c2-chart-label">{{ bar.label }}</span>
            <div class="c2-chart-bar-wrap">
              <div class="c2-chart-bar" :style="{ width: bar.pct + '%' }">{{ bar.value }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Reports Table -->
    <div class="c2-card" style="margin-top:20px;padding:0">
      <div style="padding:16px 20px;border-bottom:1px solid var(--c2-border)">
        <h3 class="c2-card-title" style="margin:0">📋 Detailed Report</h3>
      </div>
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr><th>Merchant</th><th>Bookings</th><th>Revenue (SAR)</th><th>Avg Booking</th><th>Rating</th><th>Trend</th></tr>
          </thead>
          <tbody>
            <tr v-for="row in tableData" :key="row.merchant">
              <td><strong>{{ row.merchant }}</strong></td>
              <td>{{ row.bookings }}</td>
              <td>{{ row.revenue.toLocaleString() }}</td>
              <td>{{ Math.round(row.revenue / row.bookings) }}</td>
              <td><span class="c2-stars" style="font-size:0.8rem">{{ '★'.repeat(row.rating) }}</span></td>
              <td><span :style="{ color: row.trend > 0 ? '#27ae60' : '#e74c3c', fontWeight: 700 }">{{ row.trend > 0 ? '↑' : '↓' }} {{ Math.abs(row.trend) }}%</span></td>
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
  name: 'C2Reports',
  components: { Company2Layout },
  data() {
    return {
      dateFrom: '2025-01-01', dateTo: '2025-04-09',
      reportCards: [
        { label: 'Total Revenue', value: 'SAR 84,500', sub: '+18% vs last period', color: '#27ae60' },
        { label: 'Total Bookings', value: '1,248', sub: '+12% vs last period', color: '#00aaff' },
        { label: 'Total Fees', value: 'SAR 8,450', sub: '10% commission', color: '#f39c12' },
        { label: 'Active Merchants', value: '42', sub: '6 new this period', color: '#9b59b6' },
        { label: 'Avg Rating', value: '4.3 ★', sub: 'Across all merchants', color: '#e74c3c' },
        { label: 'Cancellation Rate', value: '6.4%', sub: '-2% vs last period', color: '#1abc9c' }
      ],
      merchantRevenue: [
        { label: 'Beta Eats', value: 28400, pct: 100, color: '#00aaff' },
        { label: 'Zeta Fashion', value: 19200, pct: 68, color: '#27ae60' },
        { label: 'Delta Health', value: 15600, pct: 55, color: '#f39c12' },
        { label: 'Gamma Tech', value: 12300, pct: 43, color: '#9b59b6' },
        { label: 'Epsilon Srv.', value: 9000, pct: 32, color: '#e74c3c' }
      ],
      bookingsByCategory: [
        { label: 'Food & Bev.', value: 412, pct: 100 },
        { label: 'Beauty', value: 287, pct: 70 },
        { label: 'Healthcare', value: 218, pct: 53 },
        { label: 'Retail', value: 196, pct: 48 },
        { label: 'Technology', value: 135, pct: 33 }
      ],
      tableData: [
        { merchant: 'Beta Eats', bookings: 412, revenue: 28400, rating: 5, trend: 22 },
        { merchant: 'Zeta Fashion', bookings: 287, revenue: 19200, rating: 4, trend: 15 },
        { merchant: 'Delta Health', bookings: 218, revenue: 15600, rating: 5, trend: 8 },
        { merchant: 'Gamma Tech', bookings: 196, revenue: 12300, rating: 3, trend: -4 },
        { merchant: 'Epsilon Services', bookings: 135, revenue: 9000, rating: 4, trend: 11 }
      ]
    };
  },
  methods: {
    resetDates() { this.dateFrom = '2025-01-01'; this.dateTo = '2025-04-09'; }
  }
};
</script>
<style scoped>.c2-stars { color: #f39c12; }</style>
