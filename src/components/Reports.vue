<template>
  <div :class="['reports-container', { dark: isDarkMode }]">
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <div class="logo-section">
        <img src="@/assets/L3bnaIMG/L3bnaLogo.png" alt="Logo" class="logo" />
        <h1>L3bna</h1>
      </div>
      <nav class="navigation">
        <router-link to="/dashboard">          <img src="@/assets/L3bnaIMG/homeL3.png" alt="Home Icon" class="nav-icon" />
{{ $t('notificationSettings.home') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/profL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.merchant') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/catL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.categories') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/SerL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.servicesLink') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/BookingL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.booking') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/ReviewsL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.reviews') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/ContactL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.contactUs') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/pricingL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.pricing') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/AdsL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.ads') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/ContentL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.content') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/SerL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.settlement') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/ReportsL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.reports') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/PermisionsL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.permissions') }}</router-link>
        <router-link to="/errorpage"><img src="@/assets/L3bnaIMG/SettingL3.png" alt="Home Icon" class="nav-icon" />{{ $t('notificationSettings.setting') }}</router-link>
      </nav>
      <div class="settings">
        <div class="language-toggle">
          <label @click="setLocale('ar')">{{ $t('notificationSettings.language') }}</label>
          <label @click="setLocale('en')">English</label>
        </div>
        <div class="mode-toggle">
          <label>{{ isDarkMode ? $t('notificationSettings.nightMode') : $t('notificationSettings.language') }}</label>
          <input type="checkbox" v-model="isDarkMode" @change="toggleDarkMode" />
        </div>
      </div>
    </aside>
    <main class="content">
      <header class="header">
      
        <div class="header-row first-line">
                <h2>{{ $t('reports.title') }}</h2>
<br>
          <div class="icons">
          <br>
            <img src="@/assets/L3bnaIMG/notficationL3.png" alt="Notifications" class="icon" />
            <img src="@/assets/L3bnaIMG/ChatL3.png" alt="Chat" class="icon" />
          </div>
          <div class="user-info">
            <img src="@/assets/L3bnaIMG/AvatarL3.png" alt="User Avatar" class="avatar" />
            <span>{{ username }}</span>
          </div>
        </div>
        <div class="header-row second-line">
          <div class="date-select">
            <label class="date-label">{{ $t('reports.from') }}</label>
            <input type="date"  v-model="startDate" placeholder="From" />
            <label class="date-label">{{ $t('reports.to') }}</label>
            <input type="date" v-model="endDate" placeholder="To" class="date-input" />
          </div>
          <div class="actions">
            <button class="export-button">{{ $t('reports.exportPdf') }}</button>
          
            <button class="export-button">{{ $t('reports.exportExcel') }}</button>
          </div>
        </div>
      </header>
      <section class="reports">
        <div class="report-cards">
          <div :class="['report-card', { dark: isDarkMode }]" v-for="report in reports" :key="report.id">
            <input type="checkbox" v-model="report.selected" />
            <div class="report-content">
              <h3>{{ report.title }}</h3>
              <p>{{ report.description }}</p>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

/* eslint-disable vue/multi-word-component-names */
export default {
  name: 'Reports',
  data() {
    return {
      username: '',
      startDate: '',
      endDate: '',
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
  },
  computed: {
    ...mapState(['locale', 'isDarkMode'])
  },
  methods: {
    ...mapActions(['setLocale', 'toggleDarkMode'])
  }
};
</script>

<style scoped>
.reports-container {
  display: flex;
  height: 100vh;
  background-color: white;
  color: #000;
}

.reports-container.dark {
  background-color: #121212;
  color: #fff;
}

.sidebar {
  width: 20%;
  background-color: #f5f5f5;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  overflow-y: auto; /* Make the sidebar scrollable */
}

.sidebar.dark {
  background-color: #333;
  color: #fff; /* Set the text color to white for dark mode */
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo {
  width: 50px;
  height: 50px;
  margin-bottom: 10px;
}

.navigation {
  width: 100%;
  margin: 20px 0;
}

.navigation a {
  display: block;
  padding: 10px 20px;
  margin-bottom: 10px;
  color: inherit; /* Use the inherited color */
  text-decoration: none;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
}

.navigation a:hover, .navigation a.router-link-active {
  background-color: #ddd;
  border-left: 4px solid #00aaff;
}

.navigation a.dark:hover, .navigation a.router-link-active.dark {
  background-color: #444;
  border-left: 4px solid #ffcc00;
}

.content {
  width: 80%;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.header {
  width: 100%;
}

.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.icons {
  display: flex;
  align-items: center;
}

.icon {
  width: 24px;
  height: 24px;
  margin-right: 20px;
}

.user-info {
  display: flex;
  align-items: center;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-left: 10px;
}

.date-select {
  display: flex;
  align-items: center;
  margin-right: 20px;
}

.date-label {
  margin-right: 10px;
  font-weight: bold;
}

.date-select input {
  width: 200px;
  height: 40px;
    margin-left: 15px;

  margin-right: 15px;
  border-radius: 10px 10px 10px 10px;
}


.actions {
  display: flex;
  align-items: center;
}

.export-button {
    margin-right: 10px;
 width: Fixed (170px)px;
height: Fixed (48px)px;
top: 75px;
left: 1510px;
padding: 0px 15px 0px 15px;
gap: 8px;
border-radius: 10px 10px 10px 10px;

  padding: 10px 20px;
  background-color: #ffcc00;
  border: none;
}

.reports {
  flex: 1;
}

.reports h2 {
  margin-bottom: 20px;
}

.report-cards {
  display: flex;
  flex-wrap: wrap;
}

.report-card {
  width: 30%;
  background-color: #fff;
  margin: 10px;
  padding: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.report-card.dark {
  background-color: #444;
}

.report-card input {
  margin-right: 10px;
}

.report-content {
  display: flex;
  flex-direction: column;
}

.report-content h3 {
  margin: 0;
  font-size: 1.2em;
}

.report-content p {
  margin: 5px 0 0;
  color: #666;
}

.report-content p.dark {
  color: #bbb;
}
</style>
