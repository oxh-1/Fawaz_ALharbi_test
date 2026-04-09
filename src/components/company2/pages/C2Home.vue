<template>
  <Company2Layout page-title="Home">
    <!-- Stat Cards -->
    <div class="c2-stats-grid">
      <div class="c2-stat-card" style="border-left-color:#00aaff">
        <div class="c2-stat-label">Total Merchants</div>
        <div class="c2-stat-value">{{ stats.merchants }}</div>
        <div class="c2-stat-sub">+3 this month</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Bookings Today</div>
        <div class="c2-stat-value">{{ stats.bookings }}</div>
        <div class="c2-stat-sub">+12% vs yesterday</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Revenue (SAR)</div>
        <div class="c2-stat-value">{{ stats.revenue.toLocaleString() }}</div>
        <div class="c2-stat-sub">Last 30 days</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#e74c3c">
        <div class="c2-stat-label">Active Ads</div>
        <div class="c2-stat-value">{{ stats.ads }}</div>
        <div class="c2-stat-sub">2 ending this week</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#9b59b6">
        <div class="c2-stat-label">Pending Reviews</div>
        <div class="c2-stat-value">{{ stats.reviews }}</div>
        <div class="c2-stat-sub">Needs moderation</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#1abc9c">
        <div class="c2-stat-label">Total Categories</div>
        <div class="c2-stat-value">{{ stats.categories }}</div>
        <div class="c2-stat-sub">Across all services</div>
      </div>
    </div>

    <div class="c2-grid-2">
      <!-- Quick Links -->
      <div class="c2-card">
        <h3 class="c2-card-title">⚡ Quick Actions</h3>
        <div class="quick-links-grid">
          <router-link v-for="link in quickLinks" :key="link.to" :to="link.to" class="quick-link-item">
            <span class="quick-link-icon">{{ link.icon }}</span>
            <span>{{ link.label }}</span>
          </router-link>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="c2-card">
        <h3 class="c2-card-title">🕐 Recent Activity</h3>
        <div class="activity-feed">
          <div v-for="(item, i) in activity" :key="i" class="activity-item">
            <span class="activity-dot" :style="{ background: item.color }"></span>
            <div class="activity-text">
              <div class="activity-title">{{ item.title }}</div>
              <div class="activity-time">{{ item.time }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue Bar Chart -->
    <div class="c2-card" style="margin-top:20px">
      <h3 class="c2-card-title">📊 Monthly Revenue (SAR)</h3>
      <div class="c2-chart">
        <div v-for="bar in chartData" :key="bar.label" class="c2-chart-row">
          <span class="c2-chart-label">{{ bar.label }}</span>
          <div class="c2-chart-bar-wrap">
            <div class="c2-chart-bar" :style="{ width: bar.pct + '%' }">{{ bar.value.toLocaleString() }}</div>
          </div>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Home',
  components: { Company2Layout },
  data() {
    return {
      stats: { merchants: 48, bookings: 127, revenue: 84500, ads: 11, reviews: 23, categories: 16 },
      quickLinks: [
        { to: '/c2/merchant', icon: '🏪', label: 'Add Merchant' },
        { to: '/c2/booking', icon: '📅', label: 'View Bookings' },
        { to: '/c2/reviews', icon: '⭐', label: 'Moderate Reviews' },
        { to: '/c2/ads', icon: '📢', label: 'Manage Ads' },
        { to: '/c2/settlement', icon: '💰', label: 'Settlements' },
        { to: '/c2/reports', icon: '📈', label: 'View Reports' },
        { to: '/c2/permissions', icon: '🔐', label: 'Permissions' },
        { to: '/c2/settings', icon: '⚙️', label: 'Settings' }
      ],
      activity: [
        { title: 'New booking from Ahmed S.', time: '2 min ago', color: '#27ae60' },
        { title: 'Review submitted for Merchant #14', time: '15 min ago', color: '#f39c12' },
        { title: 'Ad "Summer Sale" activated', time: '1 hr ago', color: '#00aaff' },
        { title: 'Settlement #302 processed', time: '3 hrs ago', color: '#9b59b6' },
        { title: 'New merchant registration: Beta LLC', time: '5 hrs ago', color: '#1abc9c' },
        { title: 'Permissions updated for Manager role', time: 'Yesterday', color: '#e74c3c' }
      ],
      chartData: [
        { label: 'Jan', value: 12000, pct: 42 },
        { label: 'Feb', value: 18500, pct: 65 },
        { label: 'Mar', value: 22300, pct: 79 },
        { label: 'Apr', value: 28400, pct: 100 },
        { label: 'May', value: 19700, pct: 69 },
        { label: 'Jun', value: 24600, pct: 87 }
      ]
    };
  }
};
</script>

<style scoped>
.quick-links-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.quick-link-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
  font-size: 0.875rem;
  font-weight: 500;
  background: var(--c2-bg, #f0f2f5);
  transition: all 0.2s;
}
.quick-link-item:hover { background: rgba(0,170,255,0.1); color: #00aaff; }
.quick-link-icon { font-size: 1.1rem; }
.activity-feed { display: flex; flex-direction: column; gap: 12px; }
.activity-item { display: flex; align-items: flex-start; gap: 10px; }
.activity-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
.activity-title { font-size: 0.875rem; font-weight: 500; }
.activity-time { font-size: 0.75rem; color: var(--c2-text-muted, #7f8c8d); margin-top: 2px; }
</style>
