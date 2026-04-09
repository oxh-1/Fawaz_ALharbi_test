<template>
  <Company2Layout page-title="Reviews">
    <div class="c2-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#f39c12"><div class="c2-stat-label">Avg Rating</div><div class="c2-stat-value">{{ avgRating }}</div><div class="c2-stars">{{ starStr(avgRating) }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#27ae60"><div class="c2-stat-label">Approved</div><div class="c2-stat-value">{{ reviews.filter(r=>r.status==='Approved').length }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#f39c12"><div class="c2-stat-label">Pending</div><div class="c2-stat-value">{{ reviews.filter(r=>r.status==='Pending').length }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#e74c3c"><div class="c2-stat-label">Rejected</div><div class="c2-stat-value">{{ reviews.filter(r=>r.status==='Rejected').length }}</div></div>
    </div>

    <div class="c2-toolbar">
      <input v-model="search" class="c2-search-input" placeholder="🔍 Search reviews..." />
      <select v-model="statusFilter" class="c2-select">
        <option value="">All Status</option>
        <option>Pending</option><option>Approved</option><option>Rejected</option>
      </select>
    </div>

    <div class="reviews-list">
      <div v-for="r in filtered" :key="r.id" class="review-card c2-card">
        <div class="review-header">
          <div>
            <div class="review-author">{{ r.author }}</div>
            <div class="review-merchant">📍 {{ r.merchant }}</div>
            <div class="c2-stars">{{ starStr(r.rating) }} <span style="color:var(--c2-text-muted);font-size:0.8rem">({{ r.rating }}/5)</span></div>
          </div>
          <div style="text-align:right">
            <span :class="['c2-badge', 'c2-badge-' + r.status.toLowerCase()]">{{ r.status }}</span>
            <div class="review-date">{{ r.date }}</div>
          </div>
        </div>
        <p class="review-text">{{ r.text }}</p>
        <div class="review-actions" v-if="r.status === 'Pending'">
          <button class="c2-btn c2-btn-success c2-btn-sm" @click="setStatus(r,'Approved')">✓ Approve</button>
          <button class="c2-btn c2-btn-danger c2-btn-sm" @click="setStatus(r,'Rejected')">✕ Reject</button>
        </div>
        <div class="review-actions" v-else>
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="setStatus(r,'Pending')">↩ Reset</button>
        </div>
      </div>
      <div v-if="filtered.length === 0" class="c2-empty"><div class="c2-empty-icon">⭐</div><div class="c2-empty-text">No reviews found</div></div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Reviews',
  components: { Company2Layout },
  data() {
    return {
      search: '', statusFilter: '',
      reviews: [
        { id: 1, author: 'Ahmed S.', merchant: 'Beta Eats', rating: 5, text: 'Amazing food and super fast delivery! Highly recommended.', date: '2025-03-28', status: 'Approved' },
        { id: 2, author: 'Sara M.', merchant: 'Zeta Fashion', rating: 4, text: 'Great selection of styles. The staff was very helpful.', date: '2025-03-30', status: 'Pending' },
        { id: 3, author: 'Omar K.', merchant: 'Gamma Tech', rating: 2, text: 'Repair took too long and the communication was poor.', date: '2025-04-01', status: 'Pending' },
        { id: 4, author: 'Fatima R.', merchant: 'Delta Health', rating: 5, text: 'Professional doctors and clean environment. 10/10!', date: '2025-04-02', status: 'Approved' },
        { id: 5, author: 'Khalid A.', merchant: 'Epsilon Services', rating: 1, text: 'Spam review with inappropriate content.', date: '2025-04-03', status: 'Rejected' }
      ]
    };
  },
  computed: {
    avgRating() { return (this.reviews.reduce((a, r) => a + r.rating, 0) / this.reviews.length).toFixed(1); },
    filtered() {
      return this.reviews.filter(r => {
        const q = this.search.toLowerCase();
        const m = !q || r.author.toLowerCase().includes(q) || r.merchant.toLowerCase().includes(q) || r.text.toLowerCase().includes(q);
        const s = !this.statusFilter || r.status === this.statusFilter;
        return m && s;
      });
    }
  },
  methods: {
    starStr(n) { return '★'.repeat(Math.round(n)) + '☆'.repeat(5 - Math.round(n)); },
    setStatus(r, s) { r.status = s; }
  }
};
</script>

<style scoped>
.reviews-list { display: flex; flex-direction: column; gap: 14px; }
.review-card { padding: 18px 20px; }
.review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
.review-author { font-weight: 700; font-size: 0.95rem; }
.review-merchant { font-size: 0.78rem; color: var(--c2-text-muted); margin: 2px 0; }
.review-date { font-size: 0.75rem; color: var(--c2-text-muted); margin-top: 6px; }
.review-text { font-size: 0.875rem; color: var(--c2-text); margin: 0 0 12px 0; line-height: 1.5; }
.review-actions { display: flex; gap: 8px; }
.c2-stars { color: #f39c12; letter-spacing: 2px; }
</style>
