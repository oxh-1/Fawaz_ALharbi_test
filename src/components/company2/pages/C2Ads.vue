<template>
  <Company2Layout page-title="Ads & Campaigns">
    <!-- Header with Stats -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#00aaff">
        <div class="c2-stat-label">Total Campaigns</div>
        <div class="c2-stat-value">{{ ads.length }}</div>
        <div class="c2-stat-sub">Active & Scheduled</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Live Active Ads</div>
        <div class="c2-stat-value">{{ activeAdsCount }}</div>
        <div class="c2-stat-sub">Currently running</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Total Impressions</div>
        <div class="c2-stat-value">{{ totalImp }}</div>
        <div class="c2-stat-sub">Ad views recorded</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#9b59b6">
        <div class="c2-stat-label">Total Clicks</div>
        <div class="c2-stat-value">{{ totalClicks }}</div>
        <div class="c2-stat-sub">Avg CTR: {{ overallCTR }}%</div>
      </div>
    </div>

    <!-- Toolbar & Header -->
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Promotional Campaigns</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Manage homepage banners, sponsored merchants, and in-app advertisements.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Launch Campaign</button>
    </div>

    <!-- Campaigns Table -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr>
              <th>Campaign Name</th>
              <th>Type</th>
              <th>Duration</th>
              <th>Impressions</th>
              <th>Clicks</th>
              <th>CTR</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ad in ads" :key="ad.id">
              <td>
                <div style="font-weight:700;color:var(--c2-text)">{{ ad.name }}</div>
                <small style="color:var(--c2-text-muted)">ID #{{ ad.id }}</small>
              </td>
              <td>
                <span class="c2-badge" style="background:rgba(0,170,255,0.1);color:var(--c2-accent)">
                  {{ ad.type }}
                </span>
              </td>
              <td>
                <div style="font-size:0.82rem">{{ ad.start }} → {{ ad.end }}</div>
              </td>
              <td>
                <strong>{{ Number(ad.impressions || 0).toLocaleString() }}</strong>
              </td>
              <td>
                <strong>{{ Number(ad.clicks || 0).toLocaleString() }}</strong>
              </td>
              <td>
                <span style="font-weight:700;color:#27ae60">{{ ctr(ad) }}%</span>
              </td>
              <td>
                <span :class="['c2-badge', 'c2-badge-' + String(ad.status).toLowerCase()]">
                  {{ ad.status }}
                </span>
              </td>
              <td>
                <button
                  class="c2-btn c2-btn-sm"
                  :class="String(ad.status).toLowerCase() === 'active' ? 'c2-btn-warning' : 'c2-btn-success'"
                  @click="toggleAd(ad)"
                  style="margin-right:6px">
                  {{ String(ad.status).toLowerCase() === 'active' ? 'Pause' : 'Resume' }}
                </button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteAd(ad.id)">Delete</button>
              </td>
            </tr>

            <tr v-if="ads.length === 0">
              <td colspan="8" style="text-align:center;padding:40px;color:var(--c2-text-muted)">
                No ad campaigns currently created.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Campaign Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">Launch New Promotional Campaign</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Campaign Name *</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. National Day 50% Off Special" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Ad Type *</label>
          <select v-model="form.type" class="c2-form-select">
            <option value="Banner">Top Banner (Homepage)</option>
            <option value="Sponsored">Sponsored Merchant Card</option>
            <option value="Video">Video Promo Ad</option>
            <option value="Push">Push Notification Ad</option>
          </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Start Date *</label>
            <input type="date" v-model="form.start" class="c2-form-input" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">End Date *</label>
            <input type="date" v-model="form.end" class="c2-form-input" />
          </div>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Status</label>
          <select v-model="form.status" class="c2-form-select">
            <option value="Active">Active immediately</option>
            <option value="Scheduled">Scheduled for later</option>
            <option value="Inactive">Draft / Inactive</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="createAd">Launch Campaign</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { adApi } from '@/services/api';

export default {
  name: 'C2Ads',
  components: { Company2Layout },
  data() {
    const today = new Date().toISOString().substring(0, 10);
    const nextMonth = new Date(Date.now() + 30 * 86400000).toISOString().substring(0, 10);
    return {
      showModal: false,
      form: { name: '', type: 'Banner', start: today, end: nextMonth, status: 'Active' },
      ads: []
    };
  },
  async mounted() {
    await this.fetchData();
  },
  computed: {
    activeAdsCount() {
      return this.ads.filter(a => String(a.status).toLowerCase() === 'active').length;
    },
    totalImp() {
      const sum = this.ads.reduce((a, x) => a + Number(x.impressions || 0), 0);
      return sum.toLocaleString();
    },
    totalClicks() {
      const sum = this.ads.reduce((a, x) => a + Number(x.clicks || 0), 0);
      return sum.toLocaleString();
    },
    overallCTR() {
      const imps = this.ads.reduce((a, x) => a + Number(x.impressions || 0), 0);
      const clicks = this.ads.reduce((a, x) => a + Number(x.clicks || 0), 0);
      return imps > 0 ? ((clicks / imps) * 100).toFixed(2) : '0.00';
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await adApi.list();
        this.ads = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load ads', e);
      }
    },
    ctr(ad) {
      const imp = Number(ad.impressions || 0);
      const clk = Number(ad.clicks || 0);
      return imp > 0 ? ((clk / imp) * 100).toFixed(2) : '0.00';
    },
    async toggleAd(ad) {
      try {
        await adApi.toggle(ad.id);
        await this.fetchData();
      } catch (e) {
        console.error('Failed to toggle ad', e);
      }
    },
    async deleteAd(id) {
      if (confirm('Are you sure you want to delete this campaign?')) {
        try {
          await adApi.delete(id);
          await this.fetchData();
        } catch (e) {
          console.error('Failed to delete ad', e);
        }
      }
    },
    openModal() {
      const today = new Date().toISOString().substring(0, 10);
      const nextMonth = new Date(Date.now() + 30 * 86400000).toISOString().substring(0, 10);
      this.form = { name: '', type: 'Banner', start: today, end: nextMonth, status: 'Active' };
      this.showModal = true;
    },
    async createAd() {
      if (!this.form.name.trim()) {
        alert('Please enter a campaign name.');
        return;
      }
      try {
        await adApi.create(this.form);
        await this.fetchData();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to launch ad', e);
      }
    }
  }
};
</script>

<style scoped>
</style>
