<template>
  <Company2Layout page-title="Booking Management">
    <!-- Header with Action -->
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Appointments & Bookings</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Live overview of appointments, status updates, and calendar schedule.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openCreateModal()">+ Create Booking</button>
    </div>

    <!-- Stats Row -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#27ae60">
        <div class="c2-stat-label">Confirmed</div>
        <div class="c2-stat-value">{{ counts.confirmed }}</div>
        <div class="c2-stat-sub">Ready for service</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#f39c12">
        <div class="c2-stat-label">Pending Approval</div>
        <div class="c2-stat-value">{{ counts.pending }}</div>
        <div class="c2-stat-sub">Action required</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#00aaff">
        <div class="c2-stat-label">Completed</div>
        <div class="c2-stat-value">{{ counts.completed }}</div>
        <div class="c2-stat-sub">Past fulfilled</div>
      </div>
      <div class="c2-stat-card" style="border-left-color:#e74c3c">
        <div class="c2-stat-label">Cancelled</div>
        <div class="c2-stat-value">{{ counts.cancelled }}</div>
        <div class="c2-stat-sub">Archived / Refunded</div>
      </div>
    </div>

    <!-- Main Grid: Calendar & Booking Management List -->
    <div class="c2-grid-2">
      <!-- Interactive Mini Calendar -->
      <div class="c2-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <h3 class="c2-card-title" style="margin-bottom:0">📅 {{ currentMonthName }} {{ currentYear }}</h3>
          <div style="display:flex;gap:4px">
            <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="prevMonth">‹</button>
            <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="nextMonth">›</button>
          </div>
        </div>
        
        <div class="cal-grid">
          <div v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="cal-day-header">{{ d }}</div>
          <div v-for="blank in firstDayOfMonth" :key="'b'+blank" class="cal-day-blank"></div>
          <div
            v-for="day in daysInMonth" :key="day"
            :class="['cal-day', { 
              today: isToday(day), 
              selected: selectedDay === day, 
              'has-booking': hasBookingOnDay(day) 
            }]"
            @click="selectDay(day)">
            {{ day }}
            <span v-if="hasBookingOnDay(day)" class="cal-dot"></span>
          </div>
        </div>

        <div v-if="selectedDay" style="margin-top:14px;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:0.82rem;color:var(--c2-text-muted)">Selected Date: <strong>{{ currentYear }}-{{ String(currentMonth + 1).padStart(2, '0') }}-{{ String(selectedDay).padStart(2, '0') }}</strong></span>
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="selectedDay = null">Clear Day Filter</button>
        </div>
      </div>

      <!-- Bookings List -->
      <div class="c2-card">
        <div class="c2-section-header" style="margin-bottom:12px">
          <h3 class="c2-section-title" style="margin-bottom:0">
            {{ selectedDay ? 'Bookings for Day ' + selectedDay : 'All Bookings (' + filteredBookings.length + ')' }}
          </h3>
          <select v-model="statusFilter" class="c2-select" style="min-width:130px">
            <option value="">All Statuses</option>
            <option value="confirmed">Confirmed</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div class="booking-list">
          <div v-for="b in filteredBookings" :key="b.id" class="booking-item">
            <div class="booking-time-col">
              <span class="booking-time">{{ b.time || '10:00' }}</span>
              <small class="booking-date">{{ b.date }}</small>
            </div>
            
            <div class="booking-info">
              <div class="booking-client">{{ b.client }}</div>
              <div class="booking-service">🛠️ {{ b.service }} <span v-if="b.merchant">({{ b.merchant }})</span></div>
              <div style="font-size:0.75rem;color:var(--c2-text-muted);margin-top:2px">
                Amount: <strong style="color:var(--c2-text)">{{ Number(b.total_price || 0).toFixed(2) }} SAR</strong>
              </div>
            </div>

            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
              <span :class="['c2-badge', 'c2-badge-' + String(b.status).toLowerCase()]">
                {{ b.status }}
              </span>
              <select 
                :value="String(b.status).toLowerCase()" 
                @change="changeBookingStatus(b, $event.target.value)" 
                class="c2-select" 
                style="padding:3px 8px;font-size:0.75rem">
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div v-if="filteredBookings.length === 0" class="c2-empty" style="padding:30px;text-align:center">
            <div class="c2-empty-icon" style="font-size:2.5rem">📭</div>
            <div class="c2-empty-text" style="color:var(--c2-text-muted)">No bookings found for this selection.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Booking Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">Create Appointment / Booking</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Customer / Client Name *</label>
          <input v-model="form.client" class="c2-form-input" placeholder="e.g. Faisal Al-Harbi" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Service *</label>
          <select v-model="form.service" class="c2-form-select">
            <option v-for="s in servicesList" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Merchant Partner</label>
          <select v-model="form.merchant" class="c2-form-select">
            <option v-for="m in merchantsList" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Date *</label>
            <input v-model="form.date" type="date" class="c2-form-input" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Time *</label>
            <input v-model="form.time" type="time" class="c2-form-input" />
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Price (SAR)</label>
            <input v-model="form.total_price" type="number" class="c2-form-input" placeholder="150.00" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Status</label>
            <select v-model="form.status" class="c2-form-select">
              <option value="confirmed">Confirmed</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveBooking">Save Appointment</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { bookingApi, serviceApi, merchantApi } from '@/services/api';

export default {
  name: 'C2Booking',
  components: { Company2Layout },
  data() {
    const now = new Date();
    return {
      currentMonth: now.getMonth(),
      currentYear: now.getFullYear(),
      todayDay: now.getDate(),
      selectedDay: null,
      statusFilter: '',
      showModal: false,
      form: {
        client: '',
        service: 'Gourmet Burger',
        merchant: 'Beta Eats',
        date: now.toISOString().substring(0, 10),
        time: '12:00',
        total_price: 120,
        status: 'confirmed'
      },
      bookings: [],
      servicesList: ['Gourmet Burger', 'Laptop Repair', 'Full Body Checkup', 'Hair & Styling', 'Personal Training', 'VIP Car Detailing'],
      merchantsList: ['Acme Store', 'Beta Eats', 'Gamma Tech', 'Delta Health', 'Epsilon Services', 'Zeta Fashion']
    };
  },
  async mounted() {
    await this.fetchBookings();
    this.fetchOptions();
  },
  computed: {
    currentMonthName() {
      return new Date(this.currentYear, this.currentMonth).toLocaleString('en', { month: 'long' });
    },
    daysInMonth() {
      return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
    },
    firstDayOfMonth() {
      return new Array(new Date(this.currentYear, this.currentMonth, 1).getDay());
    },
    counts() {
      return {
        confirmed: this.bookings.filter(b => String(b.status).toLowerCase() === 'confirmed').length,
        pending: this.bookings.filter(b => String(b.status).toLowerCase() === 'pending').length,
        completed: this.bookings.filter(b => String(b.status).toLowerCase() === 'completed').length,
        cancelled: this.bookings.filter(b => String(b.status).toLowerCase() === 'cancelled').length,
      };
    },
    filteredBookings() {
      return this.bookings.filter(b => {
        let dayMatch = true;
        if (this.selectedDay) {
          const bDay = b.day || (b.date ? parseInt(b.date.split('-')[2], 10) : null);
          dayMatch = bDay === this.selectedDay;
        }
        const statusMatch = !this.statusFilter || String(b.status).toLowerCase() === this.statusFilter.toLowerCase();
        return dayMatch && statusMatch;
      });
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
    async fetchOptions() {
      try {
        const [srv, m] = await Promise.all([
          serviceApi.list().catch(() => []),
          merchantApi.list().catch(() => [])
        ]);
        const srvArr = Array.isArray(srv) ? srv : (srv.data || []);
        const mArr = Array.isArray(m) ? m : (m.data || []);
        if (srvArr.length > 0) this.servicesList = srvArr.map(s => s.name);
        if (mArr.length > 0) this.merchantsList = mArr.map(m => m.name);
      } catch (e) {
        console.error(e);
      }
    },
    prevMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear--;
      } else {
        this.currentMonth--;
      }
      this.selectedDay = null;
    },
    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear++;
      } else {
        this.currentMonth++;
      }
      this.selectedDay = null;
    },
    isToday(day) {
      const now = new Date();
      return day === now.getDate() && this.currentMonth === now.getMonth() && this.currentYear === now.getFullYear();
    },
    selectDay(day) {
      this.selectedDay = this.selectedDay === day ? null : day;
    },
    hasBookingOnDay(day) {
      return this.bookings.some(b => {
        const bDay = b.day || (b.date ? parseInt(b.date.split('-')[2], 10) : null);
        return bDay === day;
      });
    },
    openCreateModal() {
      const now = new Date();
      this.form = {
        client: '',
        service: this.servicesList[0] || 'General Service',
        merchant: this.merchantsList[0] || 'Partner Merchant',
        date: now.toISOString().substring(0, 10),
        time: '10:00',
        total_price: 150,
        status: 'confirmed'
      };
      this.showModal = true;
    },
    async saveBooking() {
      if (!this.form.client.trim()) {
        alert('Please enter a client name.');
        return;
      }
      try {
        await bookingApi.create(this.form);
        await this.fetchBookings();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to create booking', e);
      }
    },
    async changeBookingStatus(b, status) {
      try {
        await bookingApi.updateStatus(b.id, status);
        b.status = status;
      } catch (e) {
        console.error('Failed to update booking status', e);
      }
    }
  }
};
</script>

<style scoped>
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  margin-top: 12px;
}
.cal-day-header {
  text-align: center;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--c2-text-muted);
  padding: 6px 0;
}
.cal-day {
  text-align: center;
  padding: 8px 2px;
  font-size: 0.85rem;
  border-radius: 8px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
  font-weight: 500;
}
.cal-day:hover {
  background: rgba(0, 170, 255, 0.1);
  color: var(--c2-accent);
}
.cal-day.today {
  background: var(--c2-accent);
  color: white;
  font-weight: 700;
}
.cal-day.selected:not(.today) {
  background: rgba(0, 170, 255, 0.2);
  color: var(--c2-accent);
  font-weight: 700;
  outline: 2px solid var(--c2-accent);
}
.cal-day.has-booking:not(.today) {
  font-weight: 700;
}
.cal-dot {
  position: absolute;
  bottom: 2px;
  left: 50%;
  transform: translateX(-50%);
  width: 5px;
  height: 5px;
  background: #f39c12;
  border-radius: 50%;
}
.booking-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 380px;
  overflow-y: auto;
}
.booking-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 10px;
  background: var(--c2-bg, #f0f2f5);
  transition: transform 0.15s;
}
.booking-item:hover {
  transform: translateX(2px);
}
.booking-time-col {
  display: flex;
  flex-direction: column;
  min-width: 54px;
}
.booking-time {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--c2-accent);
}
.booking-date {
  font-size: 0.7rem;
  color: var(--c2-text-muted);
}
.booking-info {
  flex: 1;
}
.booking-client {
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--c2-text);
}
.booking-service {
  font-size: 0.78rem;
  color: var(--c2-text-muted);
  margin-top: 1px;
}
</style>
