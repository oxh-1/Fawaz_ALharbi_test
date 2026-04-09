<template>
  <Company2Layout page-title="Booking">
    <!-- Stats row -->
    <div class="c2-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <div class="c2-stat-card" style="border-left-color:#27ae60"><div class="c2-stat-label">Confirmed</div><div class="c2-stat-value">{{ counts.confirmed }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#f39c12"><div class="c2-stat-label">Pending</div><div class="c2-stat-value">{{ counts.pending }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#e74c3c"><div class="c2-stat-label">Cancelled</div><div class="c2-stat-value">{{ counts.cancelled }}</div></div>
      <div class="c2-stat-card" style="border-left-color:#00aaff"><div class="c2-stat-label">Today</div><div class="c2-stat-value">{{ counts.today }}</div></div>
    </div>

    <div class="c2-grid-2">
      <!-- Mini Calendar -->
      <div class="c2-card">
        <h3 class="c2-card-title">📅 {{ currentMonthName }} {{ currentYear }}</h3>
        <div class="cal-grid">
          <div v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="cal-day-header">{{ d }}</div>
          <div v-for="blank in firstDayOfMonth" :key="'b'+blank" class="cal-day-blank"></div>
          <div
            v-for="day in daysInMonth" :key="day"
            :class="['cal-day', { today: day===todayDay, 'has-booking': hasBooking(day) }]"
            @click="selectedDay = day">
            {{ day }}
            <span v-if="hasBooking(day)" class="cal-dot"></span>
          </div>
        </div>
      </div>

      <!-- Booking List -->
      <div class="c2-card">
        <div class="c2-section-header">
          <h3 class="c2-section-title">{{ selectedDay ? 'Bookings — Day ' + selectedDay : 'All Bookings' }}</h3>
          <select v-model="statusFilter" class="c2-select">
            <option value="">All</option>
            <option>Confirmed</option>
            <option>Pending</option>
            <option>Cancelled</option>
          </select>
        </div>
        <div class="booking-list">
          <div v-for="b in filteredBookings" :key="b.id" class="booking-item">
            <div class="booking-time">{{ b.time }}</div>
            <div class="booking-info">
              <div class="booking-client">{{ b.client }}</div>
              <div class="booking-service">{{ b.service }}</div>
            </div>
            <span :class="['c2-badge', 'c2-badge-' + b.status.toLowerCase()]">{{ b.status }}</span>
            <select :value="b.status" @change="updateStatus(b, $event.target.value)" class="c2-select" style="width:100px;font-size:0.75rem">
              <option>Confirmed</option><option>Pending</option><option>Cancelled</option>
            </select>
          </div>
          <div v-if="filteredBookings.length === 0" class="c2-empty">
            <div class="c2-empty-icon">📭</div>
            <div class="c2-empty-text">No bookings for this selection</div>
          </div>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Booking',
  components: { Company2Layout },
  data() {
    const now = new Date();
    return {
      currentMonth: now.getMonth(), currentYear: now.getFullYear(),
      todayDay: now.getDate(), selectedDay: null, statusFilter: '',
      bookings: [
        { id: 1, day: 3, time: '09:00', client: 'Ahmed Saleh', service: 'Hair Styling', status: 'Confirmed' },
        { id: 2, day: 3, time: '11:30', client: 'Sara Ali', service: 'Full Checkup', status: 'Pending' },
        { id: 3, day: 7, time: '14:00', client: 'Omar Khalid', service: 'Personal Training', status: 'Confirmed' },
        { id: 4, day: 10, time: '10:00', client: 'Fatima M.', service: 'Gourmet Burger', status: 'Cancelled' },
        { id: 5, day: 10, time: '13:00', client: 'Hassan R.', service: 'Laptop Repair', status: 'Pending' },
        { id: 6, day: 15, time: '09:30', client: 'Noura K.', service: 'Hair Styling', status: 'Confirmed' }
      ]
    };
  },
  computed: {
    currentMonthName() { return new Date(this.currentYear, this.currentMonth).toLocaleString('en', { month: 'long' }); },
    daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
    firstDayOfMonth() { return new Array(new Date(this.currentYear, this.currentMonth, 1).getDay()); },
    counts() {
      return {
        confirmed: this.bookings.filter(b => b.status === 'Confirmed').length,
        pending: this.bookings.filter(b => b.status === 'Pending').length,
        cancelled: this.bookings.filter(b => b.status === 'Cancelled').length,
        today: this.bookings.filter(b => b.day === this.todayDay).length
      };
    },
    filteredBookings() {
      return this.bookings.filter(b => {
        const dayMatch = !this.selectedDay || b.day === this.selectedDay;
        const statusMatch = !this.statusFilter || b.status === this.statusFilter;
        return dayMatch && statusMatch;
      });
    }
  },
  methods: {
    hasBooking(day) { return this.bookings.some(b => b.day === day); },
    updateStatus(b, val) { b.status = val; }
  }
};
</script>

<style scoped>
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-top: 12px; }
.cal-day-header { text-align: center; font-size: 0.72rem; font-weight: 700; color: var(--c2-text-muted); padding: 4px 0; }
.cal-day-blank { }
.cal-day { text-align: center; padding: 6px 2px; font-size: 0.82rem; border-radius: 6px; cursor: pointer; position: relative; transition: background 0.2s; }
.cal-day:hover { background: rgba(0,170,255,0.1); color: #00aaff; }
.cal-day.today { background: #00aaff; color: white; font-weight: 700; }
.cal-day.has-booking:not(.today) { font-weight: 700; color: #00aaff; }
.cal-dot { position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 5px; height: 5px; background: #f39c12; border-radius: 50%; }
.booking-list { display: flex; flex-direction: column; gap: 10px; max-height: 340px; overflow-y: auto; }
.booking-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: var(--c2-bg, #f0f2f5); }
.booking-time { font-size: 0.78rem; font-weight: 700; color: #00aaff; min-width: 44px; }
.booking-info { flex: 1; }
.booking-client { font-size: 0.875rem; font-weight: 600; }
.booking-service { font-size: 0.75rem; color: var(--c2-text-muted); }
</style>
