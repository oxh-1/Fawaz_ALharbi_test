<template>
  <div :class="['notification-settings-container', { dark: isDarkMode }]">
    <aside :class="['sidebar', { dark: isDarkMode }]">
      <div class="logo-section">
        <img src="@/assets/Gittax/logo1.png" alt="Gittax Logo" class="logo" />
        <h1>Gittax</h1>
      </div>
      <nav class="navigation">
        <router-link to="/dashboard" class="nav-item">
          <img src="@/assets/Gittax/Home.png" alt="Home Icon" class="nav-icon" />
          <span>{{ $t('notificationSettings.home') }}</span>
        </router-link>
      <div v-if="userDropdownVisible" class="user-dropdown">
  <router-link to="/profile">{{ $t('notificationSettings.profile') }}</router-link>
  <button @click="logout">{{ $t('notificationSettings.logout') }}</button>
</div>


        <div v-if="dropdownVisible" class="dropdown-menu">
          <router-link to="/errorpage" class="dropdown-item">{{ $t('notificationSettings.invoices') }}</router-link>
          <router-link to="/reports" class="dropdown-item">{{ $t('notificationSettings.reports') }}</router-link>
        </div>
        <router-link to="/errorpage" class="nav-item">
          <img src="@/assets/Gittax/Services.png" alt="Services Icon" class="nav-icon" />
          <span>{{ $t('notificationSettings.servicesLink') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item">
          <img src="@/assets/Gittax/Videos.png" alt="Videos Icon" class="nav-icon" />
          <span>{{ $t('notificationSettings.videos') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item">
          <img src="@/assets/Gittax/Affiliate.png" alt="Affiliate Icon" class="nav-icon" />
          <span>{{ $t('notificationSettings.affiliate') }}</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item">
          <img src="@/assets/Gittax/Contact.png" alt="Contact Icon" class="nav-icon" />
          <span>{{ $t('notificationSettings.contactUs') }}</span>
          <span class="badge">10+</span>
        </router-link>
        <router-link to="/errorpage" class="nav-item">
          <img src="@/assets/Gittax/Permission.png" alt="Permissions Icon" class="nav-icon" />
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
        <button class="download-button">{{ $t('notificationSettings.downloadApps') }}</button>
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
              <router-link to="/profile">{{ $t('notificationSettings.profile') }}</router-link>
                <button @click="logout">{{ $t('dashboard.logout') }}</button>
            </div>
          </div>
           <p><br></p>
        </div>
      </header>
      
      <section class="notification-settings">
                <button @click="showTestimonialPopup = true" class="add-testimonial-button">{{ $t('notificationSettings.addTestimonial') }}</button>

        <div class="notification-item" v-for="notification in notifications" :key="notification.id">
          <label class="switch">
            <input type="checkbox" v-model="notification.enabled" />
            <span class="slider"></span>
          </label>
          <div class="notification-text">
            <h3>{{ notification.title }}</h3>
            <p>{{ notification.description }}</p>
          </div>
        </div>
      </section>
      <AddTestimonial v-if="showTestimonialPopup" @close="showTestimonialPopup = false" @save="saveTestimonial" />
      <section class="testimonials">
        <h2>{{ $t('notificationSettings.testimonials') }}</h2>
        <div class="testimonial-item" v-for="testimonial in testimonials" :key="testimonial.id">
          <h3>{{ testimonial.username }}</h3>
          <p>{{ testimonial.companyName }}</p>
          <p>{{ testimonial.content }}</p>
        </div>
      </section>
    </main>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import AddTestimonial from './AddTestimonial.vue';

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
      notifications: [
        { id: 1, title: this.$t('notificationSettings.systemNotification'), description: this.$t('notificationSettings.systemNotificationDesc'), enabled: true },
        { id: 2, title: this.$t('notificationSettings.billingCreated'), description: this.$t('notificationSettings.billingCreatedDesc'), enabled: true },
        { id: 3, title: this.$t('notificationSettings.backupMaker'), description: this.$t('notificationSettings.backupMakerDesc'), enabled: true },
        { id: 4, title: this.$t('notificationSettings.gotFreeMonth'), description: this.$t('notificationSettings.gotFreeMonthDesc'), enabled: true }
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
    ...mapState(['isDarkMode'])
  },
  methods: {
    ...mapActions(['toggleDarkMode']),
    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.$i18n.locale = newLocale;
      this.isArabic = !this.isArabic;
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
    }
  }
};
</script>

<style scoped>
.notification-settings-container {
  display: flex;
  height: 100vh;
  background-color: white;
  color: #000;
}

.notification-settings-container.dark {
  background-color: #2c2c2c;
  color: #fff;
}

.sidebar {
  width: 20%;
  background-color: #f5f5f5;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  overflow-y: auto;
}

.sidebar.dark {
  background-color: #333;
  color: #fff;
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

.nav-item {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  margin-bottom: 10px;
  color: inherit;
  text-decoration: none;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
}

.nav-item:hover, .nav-item.router-link-active {
  background-color: #ddd;
  border-left: 4px solid #00aaff;
}

.nav-item.dark:hover, .nav-item.router-link-active.dark {
  background-color: #444;
  border-left: 4px solid #ffcc00;
}

.nav-icon {
  width: 24px;
  height: 24px;
  margin-right: 10px;
}

.dropdown {
  cursor: pointer;
}

.dropdown-icon {
  margin-left: auto;
}

.dropdown-menu {
  display: flex;
  flex-direction: column;
  padding-left: 20px;
}

.dropdown-item {
  padding: 10px 20px;
  color: inherit;
  text-decoration: none;
  transition: all 0.3s ease;
}

.dropdown-item:hover {
  background-color: #ddd;
}

.settings {
  width: 100%;
  margin-top: auto;
}

.language-toggle, .mode-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  width: 100%;
}

.add-testimonial-button {
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
.download-button {
  width: 100%;
  padding: 10px;
  background-color: #ffcc00;
  border: none;
  color: #333;
  cursor: pointer;
}

.content {
  width: 80%;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.left-header {
  display: flex;
  align-items: center;
}

.right-header {
  display: flex;
  align-items: center;
  position: relative;
}

.notification-icon {
  width: 24px;
  height: 24px;
  margin-right: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 10px;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  margin-left: 10px;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  width: 150px;
  z-index: 1000;
}

.user-dropdown a {
  display: block;
  padding: 10px;
  text-decoration: none;
  color: #333;
  transition: background 0.3s ease;
}

.user-dropdown a:hover {
  background-color: #f5f5f5;
}

.add-testimonial-button {
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

.notification-settings {
  flex: 1;
}

.notification-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.notification-text {
  margin-left: 20px;
}

.notification-text h3 {
  margin: 0;
  font-size: 1.2em;
}

.notification-text p {
  margin: 5px 0 0;
  color: #bbb;
}

.testimonials {
  margin-top: 20px;
}

.testimonial-item {
  background-color: #444;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 10px;
}

/* Custom switch styles */
.switch {
  position: relative;
  display: inline-block;
  width: 51px;
  height: 31px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 31px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 23px;
  width: 23px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #6200ea;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

/* Badge style */
.badge {
  background-color: #ff5722;
  color: white;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.8em;
  margin-left: 10px;
}
</style>
