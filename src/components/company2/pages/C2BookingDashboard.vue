<template>
  <Company2Layout page-title="Customer Booking Dashboard">
    <!-- Header with Stats -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));margin-bottom:24px">
      <div class="c2-stat-card" style="border-left-color:#00aaff">
        <div class="c2-stat-label">Upcoming Bookings</div>
        <div class="c2-stat-value">{{ upcomingBookings.length }}</div>
        <div class="c2-stat-sub">Active reservations</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Completed Services</div>
        <div class="c2-stat-value">{{ completedBookings.length }}</div>
        <div class="c2-stat-sub">Successfully fulfilled</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Total Spend (SAR)</div>
        <div class="c2-stat-value">SAR {{ totalSpent }}</div>
        <div class="c2-stat-sub">Lifetime customer volume</div>
      </div>
    </div>

    <!-- Tab Bar & Search Filter -->
    <div class="c2-card" style="margin-bottom:20px;padding:16px 20px">
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
        <div class="c2-tabs" style="margin-bottom:0">
          <button
            :class="['c2-tab', { active: activeTab === 'all' }]"
            @click="activeTab = 'all'">
            All ({{ filteredBookings.length }})
          </button>
          <button
            :class="['c2-tab', { active: activeTab === 'upcoming' }]"
            @click="activeTab = 'upcoming'">
            📅 Upcoming ({{ upcomingBookings.length }})
          </button>
          <button
            :class="['c2-tab', { active: activeTab === 'completed' }]"
            @click="activeTab = 'completed'">
            ✓ Completed ({{ completedBookings.length }})
          </button>
          <button
            :class="['c2-tab', { active: activeTab === 'cancelled' }]"
            @click="activeTab = 'cancelled'">
            ✕ Cancelled ({{ cancelledBookings.length }})
          </button>
        </div>

        <div style="display:flex;gap:10px;align-items:center">
          <input
            v-model="search"
            class="c2-search-input"
            placeholder="🔍 Search service, merchant..."
            style="font-size:0.85rem;width:220px"
          />
        </div>
      </div>
    </div>

    <!-- Booking Cards Grid -->
    <div class="dashboard-booking-grid">
      <div v-for="b in tabBookings" :key="b.id" class="c2-card booking-dash-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
          <div>
            <span class="service-tag">🛠️ {{ b.service || 'Service' }}</span>
            <h3 style="font-size:1.05rem;font-weight:700;margin:6px 0 2px 0">{{ b.merchant || 'Partner Merchant' }}</h3>
            <div style="font-size:0.8rem;color:var(--c2-text-muted)">Client: {{ b.client }}</div>
          </div>

          <span :class="['c2-badge', 'c2-badge-' + String(b.status).toLowerCase()]">
            {{ b.status }}
          </span>
        </div>

        <div class="booking-meta-box">
          <div>📅 Date: <strong>{{ b.date || (b.scheduled_at ? b.scheduled_at.substring(0, 10) : '2026-08-20') }}</strong></div>
          <div>⏰ Time: <strong>{{ b.time || '14:00' }}</strong></div>
          <div>💰 Price: <strong style="color:var(--c2-accent)">SAR {{ Number(b.total_price || 120).toFixed(2) }}</strong></div>
        </div>

        <!-- Action Buttons depending on status -->
        <div style="display:flex;gap:8px;margin-top:auto;flex-wrap:wrap">
          <template v-if="String(b.status).toLowerCase() !== 'cancelled' && String(b.status).toLowerCase() !== 'completed'">
            <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openReschedule(b)" style="flex:1">
              🗓️ Reschedule
            </button>
            <button class="c2-btn c2-btn-danger c2-btn-sm" @click="openCancel(b)" style="flex:1">
              ✕ Cancel
            </button>
          </template>

          <template v-else-if="String(b.status).toLowerCase() === 'completed'">
            <button class="c2-btn c2-btn-primary c2-btn-sm" @click="openReview(b)" style="width:100%">
              ⭐ Leave a Review
            </button>
          </template>

          <template v-else>
            <span style="font-size:0.8rem;color:var(--c2-danger);font-weight:600">
              Cancelled: {{ b.cancellation_reason || 'Customer request' }}
            </span>
          </template>
        </div>
      </div>

      <div v-if="tabBookings.length === 0" class="c2-empty" style="grid-column:1/-1;padding:60px 20px;text-align:center">
        <div class="c2-empty-icon" style="font-size:3rem;margin-bottom:10px">📦</div>
        <div class="c2-empty-text" style="color:var(--c2-text-muted);font-size:1.05rem">No appointments found in this section.</div>
        <router-link to="/c2/booking" class="c2-btn c2-btn-primary" style="margin-top:14px;display:inline-block">
          View All Bookings
        </router-link>
      </div>
    </div>

    <!-- Reschedule Modal -->
    <div v-if="showRescheduleModal" class="c2-modal-overlay" @click.self="showRescheduleModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">🗓️ Reschedule Appointment</h3>
        <button class="c2-modal-close" @click="showRescheduleModal=false">✕</button>

        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-bottom:14px">
          Select a new preferred date and time for <strong>{{ activeBooking.service }}</strong> with <strong>{{ activeBooking.merchant }}</strong>.
        </p>

        <div class="c2-form-group">
          <label class="c2-form-label">New Date *</label>
          <input v-model="rescheduleForm.date" type="date" class="c2-form-input" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">New Time Slot *</label>
          <select v-model="rescheduleForm.time" class="c2-form-select">
            <option value="09:00">09:00 AM</option>
            <option value="10:30">10:30 AM</option>
            <option value="12:00">12:00 PM</option>
            <option value="14:00">02:00 PM</option>
            <option value="15:30">03:30 PM</option>
            <option value="17:00">05:00 PM</option>
            <option value="19:00">07:00 PM</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showRescheduleModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="confirmReschedule">Confirm Reschedule</button>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="c2-modal-overlay" @click.self="showCancelModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">✕ Cancel Booking</h3>
        <button class="c2-modal-close" @click="showCancelModal=false">✕</button>

        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-bottom:14px">
          Are you sure you want to cancel this booking? A refund will be automatically calculated.
        </p>

        <div class="c2-form-group">
          <label class="c2-form-label">Reason for cancellation *</label>
          <select v-model="cancelReason" class="c2-form-select">
            <option value="Schedule Conflict">Schedule Conflict</option>
            <option value="Booked by Mistake">Booked by Mistake</option>
            <option value="Found Alternative">Found Alternative Service</option>
            <option value="Emergency / Illness">Personal Emergency</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showCancelModal=false">Keep Booking</button>
          <button class="c2-btn c2-btn-danger" @click="confirmCancel">Confirm Cancellation</button>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="c2-modal-overlay" @click.self="showReviewModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">⭐ Rate & Review Service</h3>
        <button class="c2-modal-close" @click="showReviewModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Your Rating</label>
          <div style="display:flex;gap:8px;font-size:1.6rem;cursor:pointer">
            <span v-for="star in 5" :key="star" @click="reviewForm.rating = star" :style="{ color: star <= reviewForm.rating ? '#f39c12' : '#ccc' }">
              ★
            </span>
          </div>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Review Comment *</label>
          <textarea v-model="reviewForm.comment" class="c2-form-input" rows="4" placeholder="How was your experience with this merchant?"></textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showReviewModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="submitReview">Submit Review</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { bookingApi, reviewApi } from '@/services/api';

export default {
  name: 'C2BookingDashboard',
  components: { Company2Layout },
  data() {
    return {
      activeTab: 'all',
      search: '',
      showRescheduleModal: false,
      showCancelModal: false,
      showReviewModal: false,
      activeBooking: null,
      rescheduleForm: { date: '', time: '12:00' },
      cancelReason: 'Schedule Conflict',
      reviewForm: { rating: 5, comment: '' },
      bookings: []
    };
  },
  async mounted() {
    await this.fetchBookings();
  },
  computed: {
    filteredBookings() {
      return this.bookings.filter(b => {
        const q = this.search.toLowerCase();
        return !q || (b.service && b.service.toLowerCase().includes(q)) ||
                     (b.merchant && b.merchant.toLowerCase().includes(q)) ||
                     (b.client && b.client.toLowerCase().includes(q));
      });
    },
    upcomingBookings() {
      return this.filteredBookings.filter(b => {
        const st = String(b.status).toLowerCase();
        return st === 'confirmed' || st === 'pending';
      });
    },
    completedBookings() {
      return this.filteredBookings.filter(b => String(b.status).toLowerCase() === 'completed');
    },
    cancelledBookings() {
      return this.filteredBookings.filter(b => String(b.status).toLowerCase() === 'cancelled');
    },
    totalSpent() {
      const sum = this.filteredBookings.reduce((acc, b) => acc + Number(b.total_price || 0), 0);
      return sum.toFixed(2);
    },
    tabBookings() {
      if (this.activeTab === 'upcoming') return this.upcomingBookings;
      if (this.activeTab === 'completed') return this.completedBookings;
      if (this.activeTab === 'cancelled') return this.cancelledBookings;
      return this.filteredBookings;
    }
  },
  methods: {
    async fetchBookings() {
      try {
        const res = await bookingApi.list();
        this.bookings = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load bookings', e);
      }
    },
    openReschedule(b) {
      this.activeBooking = b;
      this.rescheduleForm.date = b.date || new Date().toISOString().substring(0, 10);
      this.rescheduleForm.time = b.time || '14:00';
      this.showRescheduleModal = true;
    },
    async confirmReschedule() {
      if (!this.activeBooking) return;
      try {
        await bookingApi.update(this.activeBooking.id, {
          scheduled_at: `${this.rescheduleForm.date} ${this.rescheduleForm.time}:00`,
          status: 'confirmed'
        });
        await this.fetchBookings();
        this.showRescheduleModal = false;
      } catch (e) {
        console.error('Failed to reschedule', e);
      }
    },
    openCancel(b) {
      this.activeBooking = b;
      this.cancelReason = 'Schedule Conflict';
      this.showCancelModal = true;
    },
    async confirmCancel() {
      if (!this.activeBooking) return;
      try {
        await bookingApi.updateStatus(this.activeBooking.id, 'cancelled');
        await this.fetchBookings();
        this.showCancelModal = false;
      } catch (e) {
        console.error('Failed to cancel', e);
      }
    },
    openReview(b) {
      this.activeBooking = b;
      this.reviewForm = { rating: 5, comment: '' };
      this.showReviewModal = true;
    },
    async submitReview() {
      if (!this.activeBooking || !this.reviewForm.comment.trim()) {
        alert('Please write a brief comment.');
        return;
      }
      try {
        await reviewApi.create({
          author: this.activeBooking.client || 'Verified Customer',
          merchant: this.activeBooking.merchant || 'Acme Store',
          rating: this.reviewForm.rating,
          comment: this.reviewForm.comment,
          booking_id: this.activeBooking.id,
          status: 'approved'
        });
        alert('Thank you! Your review has been submitted.');
        this.showReviewModal = false;
      } catch (e) {
        console.error('Failed to submit review', e);
      }
    }
  }
};
</script>

<style scoped>
.dashboard-booking-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 18px;
}
.booking-dash-card {
  display: flex;
  flex-direction: column;
  padding: 20px;
  border-radius: 12px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.booking-dash-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.service-tag {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--c2-accent);
  background: rgba(0,170,255,0.1);
  padding: 2px 8px;
  border-radius: 10px;
}
.booking-meta-box {
  background: var(--c2-bg);
  padding: 10px 14px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.85rem;
  margin: 12px 0 16px 0;
}
</style>
