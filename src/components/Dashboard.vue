<template>
  <div :class="['dashboard-container', { dark: isDarkMode }]">
    <div class="header">
      <h2>{{ $t('dashboard.title') }}</h2>
      <div class="actions">
        <button @click="toggleDarkMode">{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</button>
        <button @click="toggleLanguage">{{ isArabic ? 'English' : 'اللغة العربية' }}</button>
        <button @click="logout">{{ $t('dashboard.logout') }}</button>
      </div>
    </div>
    <p>{{ $t('dashboard.welcome') }}, {{ user.username }}</p>
    <div class="companies">
      <div class="company-card" @click="navigateTo('login')">
        <img src="@/assets/Gittax/logo1.png" alt="Company G Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>login page </h3>
          <p></p>
          <router-link to="/notification-settings">{{ $t('dashboard.notificationSettings') }}</router-link>
        </div>
      </div>
          <div class="company-card" @click="navigateTo('signup')">
        <img src="@/assets/Gittax/logo1.png" alt="Company G Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>signup Page</h3>
          <router-link to="/notification-settings">{{ $t('dashboard.notificationSettings') }}</router-link>
        </div>
      </div>
          <div class="company-card" @click="navigateTo('notification-settings')">
        <img src="@/assets/Gittax/logo1.png" alt="Company G Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Notification Settings</h3>
          <router-link to="/notification-settings">{{ $t('dashboard.notificationSettings') }}</router-link>
        </div>
      </div>
          <div class="company-card" @click="navigateTo('notification-settings')">
        <img src="@/assets/Gittax/logo1.png" alt="Company G Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Admin page</h3>
          <router-link to="/notification-settings">{{ $t('dashboard.notificationSettings') }}</router-link>
        </div>
      </div>
      
      <div class="company-card" @click="navigateTo('reports')">
        <img src="@/assets/Gittax/logo1.png" alt="Company L3bny Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Company 2</h3>
          <router-link to="/reports">{{ $t('dashboard.reports') }}</router-link>
        </div>
      </div>
    </div>
     <div class="companies" style="margin-top: 200px;">
      <div class="company-card" @click="navigateTo('reports')">
        <img src="@/assets/Gittax/logo1.png" alt="Company L3bny Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Company 2</h3>
          <router-link to="/reports">{{ $t('dashboard.reports') }}</router-link>
        </div>
      </div>
       <div class="company-card" @click="navigateTo('reports')">
        <img src="@/assets/Gittax/logo1.png" alt="Company L3bny Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Company 2</h3>
          <router-link to="/reports">{{ $t('dashboard.reports') }}</router-link>
        </div>
      </div>
       <div class="company-card" @click="navigateTo('reports')">
        <img src="@/assets/Gittax/logo1.png" alt="Company L3bny Logo" class="company-logo" />
        <div :class="['company-info', { dark: isDarkMode }]">
          <h3>Company 2</h3>
          <router-link to="/reports">{{ $t('dashboard.reports') }}</router-link>
        </div>
      </div>
     </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'UserDashboard',
  data() {
    return {
      user: JSON.parse(localStorage.getItem('loggedInUser')),
      isArabic: this.$i18n.locale === 'ar'
    };
  },
  computed: {
    ...mapState(['isDarkMode'])
  },
  methods: {
    ...mapActions(['toggleDarkMode']),
    navigateTo(route) {
      this.$router.push(`/${route}`);
    },
    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.$i18n.locale = newLocale;
      this.isArabic = !this.isArabic;
    },
    logout() {
      localStorage.removeItem('loggedInUser');
      this.$router.push('/login');
    }
  }
};
</script>
<style scoped>
.dashboard-container {
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: white;
  color: black;
}

.dashboard-container.dark {
  background-color: #2c2c2c;
  color: white;
}

.header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.actions {
  display: flex;
}

.actions button {
  margin: 0 10px;
  padding: 10px 20px;
  cursor: pointer;
  border: none;
  border-radius: 5px;
  background-color: #00aaff;
  color: white;
  transition: background-color 0.3s;
}

.actions button:hover {
  background-color: #0088cc;
}

.companies {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
}

.company-card {
    margin-top: 10px;

  background: #f5f5f5;
  border-radius: 10px;
  padding: 20px;
  width: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.company-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.company-card.dark {
  background: #444;
}

.company-logo {
  width: 80px;
  height: 80px;
  margin-bottom: 10px;
}

.company-info {
  text-align: center;
}

.company-info h3 {
  margin-bottom: 10px;
}

.company-info a {
  color: #00aaff;
  text-decoration: none;
  font-weight: bold;
}

.company-info a:hover {
  text-decoration: underline;
}

.company-info.dark h3{
  color: black;
}
.company-info.dark a {
  color: #00aaff;
}
</style>
