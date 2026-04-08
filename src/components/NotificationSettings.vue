<template>
  <div :class="['notification-settings-container', { dark: isDarkMode }]">
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <router-link to="/dashboard">
        <div class="logo-section">
          <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
        </div>
      </router-link>

      <nav class="navigation">
        <router-link to="/dashboard" class="nav-item active">
          <div class="icon-wrapper active-icon">
            <img src="@/assets/Gittax/Home.png" alt="Home Icon" class="nav-icon" />
          </div>
          <span class="active-text">{{ $t('notificationSettings.home') }}</span>
        </router-link>
        
        <div class="nav-item aactive" @click="toggleDropdown">
          <div class="icon-wrapper">
            <img src="@/assets/Gittax/Invoicesandreports.png" alt="Invoices Icon" class="nav-icon" />
          </div>
          <span>{{ $t('notificationSettings.invoicesAndReports') }}</span>
          <img src="@/assets/Gittax/Drop.png" alt="Dropdown Icon" class="dropdown-icon" />
        </div>
        
        <div v-if="dropdownVisible" class="dropdown-menu">
          <router-link to="/errorpage" class="dropdown-item">{{ $t('notificationSettings.invoices') }}</router-link>
          <router-link to="/errorpage" class="dropdown-item">{{ $t('notificationSettings.reports') }}</router-link>
        </div>

        <router-link v-for="item in ['servicesLink', 'videos', 'affiliate', 'contactUs', 'permissions']" 
                     :key="item" to="/errorpage" class="nav-item aactive">
          <div class="icon-wrapper">
            <img :src="getIcon(item)" class="nav-icon" />
          </div>
          <span>{{ $t(`notificationSettings.${item}`) }}</span>
          <span v-if="item === 'contactUs'" class="badge">10+</span>
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
            <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
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
            <img :src="userAvatar || defaultAvatar" alt="User Avatar" class="avatar" />
            <span>{{ displayName }}</span>
            <img src="@/assets/Gittax/Drop.png" alt="Dropdown Icon" class="dropdown-icon" @click="toggleUserDropdown" />
            
            <div v-if="userDropdownVisible" class="user-dropdown">
              <router-link to="/profile">{{ $t('notificationSettings.profile') }}</router-link>
              <button @click="logout" class="dropdown-link">{{ $t('dashboard.logout') }}</button>
            </div>
          </div>
        </div>
      </header>

      <section class="notification-settings">
        <div v-if="isSystemNotificationOn" class="report-cards">
          <div :class="['report-card', { dark: isDarkMode }]" v-for="report in reportList" :key="report.id">
            <div class="report-content">
              <input type="checkbox" v-model="report.selected" @change="toggleReportSelection(report.id)" />
              <h3>{{ report.title }}</h3>
            </div>
            <p>{{ report.description }}</p>
          </div>
        </div>

        <div v-else>
          <div class="notification-item" v-for="notification in notificationList" :key="notification.id">
            <label class="switch">
              <input type="checkbox" v-model="notification.enabled" @change="syncSystemToggle(notification)" />
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
          <h3>{{ $t('login.username') }}: {{ testimonial.username }}</h3>
          <p>{{ testimonial.content }}</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AddTestimonial from './AddTestimonial.vue';
import '@/assets/styles/NotificationSettings.css'; 

export default {
  name: 'NotificationSettings',
  components: { AddTestimonial },
  data() {
    return {
      displayName: '',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      showTestimonialPopup: false,
      isArabic: false,
      dropdownVisible: false,
      userDropdownVisible: false,
      testimonials: [],
      isSystemNotificationOn: false,
      
      // These hold the "state" of the toggles
      notificationStates: { 1: false, 2: true, 3: true, 4: true },
      reportStates: { 1: false, 2: true, 3: true, 4: true, 5: true, 6: true }
    };
  },

  computed: {
    ...mapState(['isDarkMode', 'locale']),

    // Computed lists ensure text translates immediately when language changes
    notificationList() {
      return [
        { id: 1, title: this.$t('notificationSettings.systemNotification'), description: this.$t('notificationSettings.systemNotificationDesc'), enabled: this.notificationStates[1] },
        { id: 2, title: this.$t('notificationSettings.billingCreated'), description: this.$t('notificationSettings.billingCreatedDesc'), enabled: this.notificationStates[2] },
        { id: 3, title: this.$t('notificationSettings.backupMaker'), description: this.$t('notificationSettings.backupMakerDesc'), enabled: this.notificationStates[3] },
        { id: 4, title: this.$t('notificationSettings.gotFreeMonth'), description: this.$t('notificationSettings.gotFreeMonthDesc'), enabled: this.notificationStates[4] }
      ];
    },

    reportList() {
      return [
        { id: 1, title: this.$t('reports.totalSales'), description: this.$t('reports.totalSalesDesc'), selected: this.reportStates[1] },
        { id: 2, title: this.$t('reports.totalFees'), description: this.$t('reports.totalFeesDesc'), selected: this.reportStates[2] },
        { id: 3, title: this.$t('reports.playgrounds'), description: this.$t('reports.playgroundsDesc'), selected: this.reportStates[3] },
        { id: 4, title: this.$t('reports.totalSubs'), description: this.$t('reports.totalSubsDesc'), selected: this.reportStates[4] },
        { id: 5, title: this.$t('reports.clients'), description: this.$t('reports.clientsDesc'), selected: this.reportStates[5] },
        { id: 6, title: this.$t('reports.bankFee'), description: this.$t('reports.bankFeeDesc'), selected: this.reportStates[6] }
      ];
    }
  },

  mounted() {
    this.isArabic = this.$i18n.locale === 'ar';
    this.loadUser();
  },

  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),

    loadUser() {
      const user = JSON.parse(localStorage.getItem('loggedInUser'));
      if (user) {
        // FIX: Google uses .name, Manual uses .username
        this.displayName = user.name || user.username || "User";
        this.userAvatar = user.picture || ''; 
      } else {
        this.$router.push('/login');
      }
    },

    toggleLanguage() {
      const newLocale = this.isArabic ? 'ar' : 'en';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
    },

    syncSystemToggle(notif) {
      this.notificationStates[notif.id] = notif.enabled;
      if (notif.id === 1) {
        this.isSystemNotificationOn = notif.enabled;
      }
    },

    toggleReportSelection(reportId) {
      // If a report is deselected, logic to handle the system view
      if (!this.reportStates[reportId]) {
        // Optional logic: if they uncheck all, turn off system view
        const anySelected = Object.values(this.reportStates).some(v => v);
        if (!anySelected) this.isSystemNotificationOn = false;
      }
    },

    getIcon(name) {
      try { return require(`@/assets/Gittax/${name.charAt(0).toUpperCase() + name.slice(1)}.png`); }
      catch (e) { return require('@/assets/Gittax/Home.png'); }
    },

    toggleDropdown() { this.dropdownVisible = !this.dropdownVisible; },
    toggleUserDropdown() { this.userDropdownVisible = !this.userDropdownVisible; },
    
    saveTestimonial(t) {
      this.testimonials.push({ ...t, id: Date.now() });
      this.showTestimonialPopup = false;
    },

    logout() {
      this.$store.dispatch('logout');
      this.$router.push('/login');
    }
  }
};
</script>
