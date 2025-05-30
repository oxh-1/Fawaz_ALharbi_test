<template>
  <div :class="['notification-settings-container', { dark: isDarkMode }]">
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <div class="logo-section">
        <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
      </div>
      <nav class="navigation">
        <router-link to="/errorpage" class="nav-item active">
          <div class="icon-wrapper active-icon">
            <img src="@/assets/Gittax/Home.png" alt="Home Icon" class="nav-icon" />
          </div>
          <span class="active-text">{{ $t('notificationSettings.home') }}</span>
        </router-link>
        <div class="nav-item aactive" @click="toggleDropdown">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Invoicesandreports.png" alt="Invoices and Reports Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.invoicesAndReports') }}</span>
          <img src="@/assets/Gittax/Drop.png" alt="Dropdown Icon" class="dropdown-icon" />
        </div>
        <div v-if="dropdownVisible" class="dropdown-menu">
          <router-link to="/errorpage" class="dropdown-item">{{ $t('notificationSettings.invoices') }}</router-link>
          <router-link to="/errorpage" class="dropdown-item">{{ $t('notificationSettings.reports') }}</router-link>
        </div>
        <router-link to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Services.png" alt="Services Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.servicesLink') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Videos.png" alt="Videos Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.videos') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Affiliate.png" alt="Affiliate Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.affiliate') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Contact.png" alt="Contact Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.contactUs') }}</span>
          <span class="badge">10+</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Permission.png" alt="Permissions Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.permissions') }}</span>
        </router-link>
      </nav>
      <div class="settings">
        <div class="language-toggle">
          <label>{{ $t('notificationSettings.language') }}</label>
          <label class="switch">
            <input type="checkbox" v-model="isArabic" @change="toggleLanguage" />
            <span class="slider"></span>
          </label>
        </div>
        <div class="mode-toggle">
          <label>{{ $t('notificationSettings.nightMode') }}</label>
          <label class="switch">
            <input type="checkbox" v-model="isDarkMode" @change="toggleDarkMode" />
            <span class="slider"></span>
          </label>
        </div>
        <button @click="showTestimonialPopup = true" class="download-button">{{ $t('notificationSettings.downloadApps') }}</button>
      </div>
    </aside>
    <main class="content">
      <header class="header">
        <div class="left-header">
          <h2>{{ $t('notificationSettings.notificationSetting') }}</h2>
        </div>
        <div class="right-header">
          <img src="@/assets/Gittax/Notifcation.png" alt="Notification Icon" class="notification-icon" />
          <div class="user-info">
            <img src="@/assets/Gittax/avatar.png" alt="User Avatar" class="avatar" />
            <span>{{ username }}</span>
            <img src="@/assets/Gittax/Drop.png" alt="Dropdown Icon" class="dropdown-icon" @click="toggleUserDropdown" />
            <div v-if="userDropdownVisible" class="user-dropdown">
              <router-link to="/errorpage">{{ $t('notificationSettings.profile') }}</router-link>
              <button @click="logout" class="dropdown-link">{{ $t('dashboard.logout') }}</button>
            </div>
          </div>
          <p><br></p>
        </div>
      </header>

      <section class="notification-settings">
        <div v-if="isSystemNotificationOn" class="report-cards">
          <div :class="['report-card', { dark: isDarkMode }]" v-for="report in reports" :key="report.id">
            <div class="report-content">
              <input type="checkbox" v-model="report.selected" @change="toggleReportSelection(report.id)" />
              <h3>{{ report.title }}</h3>
            </div>
            <p>{{ report.description }}</p>
          </div>
        </div>

        <div v-else>
          <div class="notification-item" v-for="notification in notifications" :key="notification.id">
            <label class="switch">
              <input type="checkbox" v-model="notification.enabled" @change="checkSystemNotification" />
              <span class="slider"></span>
            </label>
            <div class="notification-text">
              <h3>{{ notification.title }}</h3>
              <p>{{ notification.description }}</p>
            </div>
          </div>
        </div>
      </section>
      <AddTestimonial v-if="showTestimonialPopup" @close="showTestimonialPopup = false" @save="saveTestimonial" />
      <section class="testimonials">
        <div class="testimonial-item" v-for="testimonial in testimonials" :key="testimonial.id">
          <h3>User Name: {{ testimonial.username }}</h3>
          <p>Company Name: {{ testimonial.companyName }}</p>
          <p>Content: {{ testimonial.content }}</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AddTestimonial from './AddTestimonial.vue';
import '@/assets/styles/NotificationSettings.css'; // Import the new CSS file

export default {
  name: 'NotificationSettings',
  components: {
    AddTestimonial
  },
  data() {
    return {
      username: '',
      showTestimonialPopup: false,
      isArabic: this.$i18n.locale === 'ar',
      dropdownVisible: false,
      userDropdownVisible: false,
      testimonials: [],
      isSystemNotificationOn: false,
      notifications: [
        { id: 1, title: this.$t('notificationSettings.systemNotification'), description: this.$t('notificationSettings.systemNotificationDesc'), enabled: false },
        { id: 2, title: this.$t('notificationSettings.billingCreated'), description: this.$t('notificationSettings.billingCreatedDesc'), enabled: true },
        { id: 3, title: this.$t('notificationSettings.backupMaker'), description: this.$t('notificationSettings.backupMakerDesc'), enabled: true },
        { id: 4, title: this.$t('notificationSettings.gotFreeMonth'), description: this.$t('notificationSettings.gotFreeMonthDesc'), enabled: true }
      ],
      reports: [
        { id: 1, title: this.$t('reports.totalSales'), description: this.$t('reports.totalSalesDesc'), selected: true },
        { id: 2, title: this.$t('reports.totalFees'), description: this.$t('reports.totalFeesDesc'), selected: true },
        { id: 3, title: this.$t('reports.playgrounds'), description: this.$t('reports.playgroundsDesc'), selected: true },
        { id: 4, title: this.$t('reports.totalSubs'), description: this.$t('reports.totalSubsDesc'), selected: true },
        { id: 5, title: this.$t('reports.clients'), description: this.$t('reports.clientsDesc'), selected: true },
        { id: 6, title: this.$t('reports.bankFee'), description: this.$t('reports.bankFeeDesc'), selected: true }
      ]
    };
  },
  created() {
    const loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
    if (loggedInUser && loggedInUser.username) {
      this.username = loggedInUser.username;
    }
    this.checkSystemNotification();
  },
  computed: {
    ...mapState(['isDarkMode'])
  },
  methods: {
    ...mapActions(['toggleDarkMode']),
toggleLanguage() {
  this.$i18n.locale = this.isArabic ? 'ar' : 'en';
},
    toggleDropdown() {
      this.dropdownVisible = !this.dropdownVisible;
    },
    toggleUserDropdown() {
      this.userDropdownVisible = !this.userDropdownVisible;
    },
    saveTestimonial(testimonial) {
      this.testimonials.push(testimonial);
      this.showTestimonialPopup = false;
    },
    logout() {
      localStorage.removeItem('loggedInUser');
      this.$router.push('/login');
    },
    checkSystemNotification() {
      this.isSystemNotificationOn = this.notifications.find(notification => notification.id === 1).enabled;
    },
    toggleReportSelection(reportId) {
      const report = this.reports.find(r => r.id === reportId);
      if (report) {
        report.selected = !report.selected;
        if (!report.selected) {
          this.isSystemNotificationOn = false;
        }
      }
    }
  },
  watch: {
    notifications: {
      handler() {
        this.checkSystemNotification();
      },
      deep: true
    }
  }
};
</script>

