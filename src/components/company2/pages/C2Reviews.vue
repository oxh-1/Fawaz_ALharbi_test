<template>
  <Company2Layout page-title="Reviews">
    <!-- Header with Stats -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Platform Rating</div>
        <div class="c2-stat-value">{{ avgRating }} / 5.0</div>
        <div class="c2-stars">{{ starStr(avgRating) }}</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Approved</div>
        <div class="c2-stat-value">{{ counts.approved }}</div>
        <div class="c2-stat-sub">Visible to public</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Pending Review</div>
        <div class="c2-stat-value">{{ counts.pending }}</div>
        <div class="c2-stat-sub">Needs moderation</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#e74c3c">
        <div class="c2-stat-label">Rejected / Flagged</div>
        <div class="c2-stat-value">{{ counts.rejected }}</div>
        <div class="c2-stat-sub">Hidden from store</div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search reviews, authors, comments..." />
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <select v-model="ratingFilter" class="c2-select">
          <option value="">All Ratings</option>
          <option value="5">5 Stars ★★★★★</option>
          <option value="4">4 Stars ★★★★☆</option>
          <option value="3">3 Stars ★★★☆☆</option>
          <option value="2">2 Stars ★★☆☆☆</option>
          <option value="1">1 Star ★☆☆☆☆</option>
        </select>
      </div>
      <span style="font-size:0.85rem;color:var(--c2-text-muted)">{{ filtered.length }} reviews</span>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
      <div v-for="r in filtered" :key="r.id" class="review-card c2-card">
        <div class="review-header">
          <div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="review-author">{{ r.author }}</span>
              <span v-if="r.verified_purchase" class="c2-badge c2-badge-active" style="font-size:0.7rem;padding:2px 6px">
                ✓ Verified Booking
              </span>
              <span v-if="r.sentiment" :class="['sentiment-chip', 'sentiment-' + r.sentiment]">
                {{ r.sentiment }}
              </span>
            </div>
            <div class="review-merchant">📍 Partner: <strong>{{ r.merchant }}</strong></div>
            <div class="c2-stars">
              {{ starStr(r.rating) }} 
              <span style="color:var(--c2-text-muted);font-size:0.8rem;margin-left:4px">({{ r.rating }}/5)</span>
            </div>
          </div>

          <div style="text-align:right">
            <span :class="['c2-badge', 'c2-badge-' + String(r.status).toLowerCase()]">
              {{ r.status }}
            </span>
            <div class="review-date">{{ r.date || (r.created_at ? r.created_at.substring(0,10) : 'Recent') }}</div>
          </div>
        </div>

        <p class="review-text">{{ r.comment || r.text }}</p>

        <!-- Merchant Response if any -->
        <div v-if="r.merchant_response" class="merchant-reply-box">
          <div style="font-size:0.75rem;font-weight:700;color:var(--c2-accent);margin-bottom:2px">
            💬 Merchant Response ({{ r.merchant }}):
          </div>
          <div style="font-size:0.85rem;color:var(--c2-text)">{{ r.merchant_response }}</div>
        </div>

        <!-- Moderation Notes if rejected -->
        <div v-if="r.moderation_notes" class="moderation-note-box">
          <strong>Moderation Note:</strong> {{ r.moderation_notes }}
        </div>

        <!-- Action Bar -->
        <div class="review-actions">
          <button 
            v-if="String(r.status).toLowerCase() !== 'approved'"
            class="c2-btn c2-btn-success c2-btn-sm" 
            @click="setStatus(r, 'approved')">
            ✓ Approve
          </button>
          
          <button 
            v-if="String(r.status).toLowerCase() !== 'rejected'"
            class="c2-btn c2-btn-danger c2-btn-sm" 
            @click="openRejectModal(r)">
            ✕ Reject
          </button>
          
          <button 
            class="c2-btn c2-btn-ghost c2-btn-sm" 
            @click="openReplyModal(r)">
            💬 {{ r.merchant_response ? 'Edit Reply' : 'Reply as Merchant' }}
          </button>

          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="deleteReview(r.id)">
            🗑️ Delete
          </button>
        </div>
      </div>

      <div v-if="filtered.length === 0" class="c2-empty" style="padding:40px;text-align:center">
        <div class="c2-empty-icon" style="font-size:3rem">⭐</div>
        <div class="c2-empty-text" style="color:var(--c2-text-muted)">No reviews match your filters.</div>
      </div>
    </div>

    <!-- Reject Reason Modal -->
    <div v-if="showRejectModal" class="c2-modal-overlay" @click.self="showRejectModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">Reject Review</h3>
        <button class="c2-modal-close" @click="showRejectModal=false">✕</button>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-bottom:12px">
          Please provide a reason for rejecting this review.
        </p>
        <div class="c2-form-group">
          <label class="c2-form-label">Reason</label>
          <textarea v-model="rejectReason" class="c2-form-input" rows="3" placeholder="e.g. Inappropriate language, false claims, spam"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button class="c2-btn c2-btn-ghost" @click="showRejectModal=false">Cancel</button>
          <button class="c2-btn c2-btn-danger" @click="confirmReject">Reject Review</button>
        </div>
      </div>
    </div>

    <!-- Reply Modal -->
    <div v-if="showReplyModal" class="c2-modal-overlay" @click.self="showReplyModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">Reply to Customer Review</h3>
        <button class="c2-modal-close" @click="showReplyModal=false">✕</button>
        <div class="c2-form-group">
          <label class="c2-form-label">Merchant Response *</label>
          <textarea v-model="replyText" class="c2-form-input" rows="4" placeholder="Thank you for your feedback! We look forward to serving you again."></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button class="c2-btn c2-btn-ghost" @click="showReplyModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveReply">Send Response</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { reviewApi } from '@/services/api';

export default {
  name: 'C2Reviews',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      statusFilter: '',
      ratingFilter: '',
      showRejectModal: false,
      showReplyModal: false,
      activeReview: null,
      rejectReason: '',
      replyText: '',
      reviews: []
    };
  },
  async mounted() {
    await this.fetchData();
  },
  computed: {
    avgRating() {
      if (this.reviews.length === 0) return '5.0';
      const sum = this.reviews.reduce((a, r) => a + Number(r.rating || 5), 0);
      return (sum / this.reviews.length).toFixed(1);
    },
    counts() {
      return {
        approved: this.reviews.filter(r => String(r.status).toLowerCase() === 'approved').length,
        pending: this.reviews.filter(r => String(r.status).toLowerCase() === 'pending').length,
        rejected: this.reviews.filter(r => String(r.status).toLowerCase() === 'rejected').length,
      };
    },
    filtered() {
      return this.reviews.filter(r => {
        const q = this.search.toLowerCase();
        const text = (r.comment || r.text || '').toLowerCase();
        const author = (r.author || '').toLowerCase();
        const merchant = (r.merchant || '').toLowerCase();
        const matchQuery = !q || text.includes(q) || author.includes(q) || merchant.includes(q);
        const matchStatus = !this.statusFilter || String(r.status).toLowerCase() === this.statusFilter.toLowerCase();
        const matchRating = !this.ratingFilter || String(r.rating) === String(this.ratingFilter);
        return matchQuery && matchStatus && matchRating;
      });
    }
  },
  methods: {
    starStr(n) {
      const rounded = Math.round(Number(n) || 5);
      return '★'.repeat(rounded) + '☆'.repeat(Math.max(0, 5 - rounded));
    },
    async fetchData() {
      try {
        const res = await reviewApi.list();
        this.reviews = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load reviews', e);
      }
    },
    async setStatus(r, status) {
      try {
        await reviewApi.moderate(r.id, status);
        r.status = status;
      } catch (e) {
        console.error('Failed to update review status', e);
      }
    },
    openRejectModal(r) {
      this.activeReview = r;
      this.rejectReason = 'Violates community guidelines';
      this.showRejectModal = true;
    },
    async confirmReject() {
      if (!this.activeReview) return;
      try {
        await reviewApi.moderate(this.activeReview.id, 'rejected', this.rejectReason);
        this.activeReview.status = 'rejected';
        this.activeReview.moderation_notes = this.rejectReason;
        this.showRejectModal = false;
      } catch (e) {
        console.error('Failed to reject review', e);
      }
    },
    openReplyModal(r) {
      this.activeReview = r;
      this.replyText = r.merchant_response || '';
      this.showReplyModal = true;
    },
    async saveReply() {
      if (!this.activeReview || !this.replyText.trim()) return;
      try {
        await reviewApi.reply(this.activeReview.id, { response: this.replyText });
        this.activeReview.merchant_response = this.replyText;
        this.showReplyModal = false;
      } catch (e) {
        console.error('Failed to save reply', e);
      }
    },
    async deleteReview(id) {
      if (confirm('Are you sure you want to permanently delete this review?')) {
        try {
          await reviewApi.delete(id);
          await this.fetchData();
        } catch (e) {
          console.error('Failed to delete review', e);
        }
      }
    }
  }
};
</script>

<style scoped>
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.review-card {
  padding: 18px 20px;
  border-radius: 12px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.review-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}
.review-author {
  font-weight: 700;
  font-size: 0.95rem;
}
.review-merchant {
  font-size: 0.8rem;
  color: var(--c2-text-muted);
  margin: 3px 0;
}
.review-date {
  font-size: 0.75rem;
  color: var(--c2-text-muted);
  margin-top: 6px;
}
.review-text {
  font-size: 0.9rem;
  color: var(--c2-text);
  margin: 0 0 12px 0;
  line-height: 1.5;
}
.review-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}
.c2-stars {
  color: #f39c12;
  letter-spacing: 2px;
  font-size: 0.95rem;
}
.merchant-reply-box {
  background: rgba(0, 170, 255, 0.07);
  border-left: 3px solid var(--c2-accent);
  padding: 10px 14px;
  border-radius: 6px;
  margin-bottom: 12px;
}
.moderation-note-box {
  background: rgba(231, 76, 60, 0.08);
  border-left: 3px solid var(--c2-danger);
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 0.8rem;
  color: var(--c2-danger);
}
.sentiment-chip {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: capitalize;
}
.sentiment-positive { background: rgba(39, 174, 96, 0.12); color: #27ae60; }
.sentiment-neutral { background: rgba(243, 156, 18, 0.12); color: #f39c12; }
.sentiment-negative { background: rgba(231, 76, 60, 0.12); color: #e74c3c; }
</style>
