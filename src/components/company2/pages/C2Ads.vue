<template>
  <Company2Layout page-title="Ads">
    <div class="c2-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#00aaff"><div class="c2-stat-label">Total Ads</div><div class="c2-stat-value">{{ ads.length }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#27ae60"><div class="c2-stat-label">Active</div><div class="c2-stat-value">{{ ads.filter(a=>a.status==='Active').length }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#f39c12"><div class="c2-stat-label">Total Impressions</div><div class="c2-stat-value">{{ totalImp }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#9b59b6"><div class="c2-stat-label">Total Clicks</div><div class="c2-stat-value">{{ totalClicks }}</div></div>
    </div>

    <div class="c2-section-header">
      <h2 class="c2-section-title">Ad Campaigns</h2>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ New Campaign</button>
    </div>

    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr><th>Campaign</th><th>Type</th><th>Start</th><th>End</th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <tr v-for="ad in ads" :key="ad.id">
              <td><strong>{{ ad.name }}</strong></td>
              <td>{{ ad.type }}</td>
              <td>{{ ad.start }}</td>
              <td>{{ ad.end }}</td>
              <td>{{ ad.impressions.toLocaleString() }}</td>
              <td>{{ ad.clicks.toLocaleString() }}</td>
              <td>{{ ctr(ad) }}%</td>
              <td><span :class="['c2-badge', 'c2-badge-' + ad.status.toLowerCase()]">{{ ad.status }}</span></td>
              <td>
                <button class="c2-btn c2-btn-sm" :class="ad.status==='Active' ? 'c2-btn-warning' : 'c2-btn-success'" @click="toggleAd(ad)" style="margin-right:6px">{{ ad.status==='Active'?'Pause':'Resume' }}</button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteAd(ad.id)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">New Ad Campaign</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        <div class="c2-form-group"><label class="c2-form-label">Campaign Name</label><input v-model="form.name" class="c2-form-input" placeholder="e.g. Summer Sale 2025" /></div>
        <div class="c2-form-group"><label class="c2-form-label">Type</label><select v-model="form.type" class="c2-form-select"><option>Banner</option><option>Video</option><option>Sponsored</option><option>Push</option></select></div>
        <div class="c2-grid-2">
          <div class="c2-form-group"><label class="c2-form-label">Start Date</label><input type="date" v-model="form.start" class="c2-form-input" /></div>
          <div class="c2-form-group"><label class="c2-form-label">End Date</label><input type="date" v-model="form.end" class="c2-form-input" /></div>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="createAd">Launch</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Ads',
  components: { Company2Layout },
  data() {
    return {
      showModal: false,
      form: { name: '', type: 'Banner', start: '', end: '' },
      ads: [
        { id: 1, name: 'Summer Sale 2025', type: 'Banner', start: '2025-06-01', end: '2025-06-30', impressions: 45200, clicks: 1830, status: 'Active' },
        { id: 2, name: 'New Merchant Promo', type: 'Sponsored', start: '2025-04-01', end: '2025-04-15', impressions: 18700, clicks: 920, status: 'Active' },
        { id: 3, name: 'App Download Push', type: 'Push', start: '2025-03-10', end: '2025-03-25', impressions: 32000, clicks: 2450, status: 'Inactive' },
        { id: 4, name: 'Eid Offers', type: 'Video', start: '2025-03-28', end: '2025-04-05', impressions: 61000, clicks: 4200, status: 'Active' }
      ]
    };
  },
  computed: {
    totalImp() { return this.ads.reduce((a, x) => a + x.impressions, 0).toLocaleString(); },
    totalClicks() { return this.ads.reduce((a, x) => a + x.clicks, 0).toLocaleString(); }
  },
  methods: {
    ctr(ad) { return ad.impressions ? ((ad.clicks / ad.impressions) * 100).toFixed(2) : '0.00'; },
    toggleAd(ad) { ad.status = ad.status === 'Active' ? 'Inactive' : 'Active'; },
    deleteAd(id) { if (confirm('Delete campaign?')) this.ads = this.ads.filter(a => a.id !== id); },
    openModal() { this.form = { name: '', type: 'Banner', start: '', end: '' }; this.showModal = true; },
    createAd() {
      if (!this.form.name.trim()) return;
      this.ads.push({ id: Date.now(), ...this.form, impressions: 0, clicks: 0, status: 'Active' });
      this.showModal = false;
    }
  }
};
</script>
