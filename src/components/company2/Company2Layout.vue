<template>
  <div :class="['c2-container', { dark: isDarkMode }]">
    <!-- Sidebar -->
    <aside :class="['c2-sidebar', { dark: isDarkMode }]">
      <div class="c2-logo-section">
        <img src="@/assets/L3bnaIMG/L3bnaLogo.png" alt="Company 2 Logo" class="c2-logo" />
        <h2 class="c2-brand-name">Company 2</h2>
      </div>

      <nav class="c2-nav">
        <router-link to="/c2/home" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/homeL3.png" class="c2-nav-icon" alt="" />
          <span>Home</span>
        </router-link>
        <router-link to="/c2/merchant" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/profL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.merchant') }}</span>
        </router-link>
        <router-link to="/c2/categories" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/catL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.categories') }}</span>
        </router-link>
        <router-link to="/c2/services" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/SerL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.servicesLink') }}</span>
        </router-link>
        <router-link to="/c2/booking" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/BookingL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.booking') }}</span>
        </router-link>
        <router-link to="/c2/reviews" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/ReviewsL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.reviews') }}</span>
        </router-link>
        <router-link to="/c2/contact" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/ContactL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.contactUs') }}</span>
        </router-link>
        <router-link to="/c2/pricing" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/pricingL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.pricing') }}</span>
        </router-link>
        <router-link to="/c2/ads" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/AdsL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.ads') }}</span>
        </router-link>
        <router-link to="/c2/content" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/ContentL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.content') }}</span>
        </router-link>
        <router-link to="/c2/settlement" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/SerL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.settlement') }}</span>
        </router-link>
        <router-link to="/c2/reports" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/ReportsL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.reports') }}</span>
        </router-link>
        <router-link to="/c2/permissions" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/PermisionsL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.permissions') }}</span>
        </router-link>
        <router-link to="/c2/settings" class="c2-nav-link">
          <img src="@/assets/L3bnaIMG/SettingL3.png" class="c2-nav-icon" alt="" />
          <span>{{ $t('notificationSettings.setting') }}</span>
        </router-link>
      </nav>

      <div class="c2-sidebar-footer">
        <div class="c2-toggle-row">
          <span>Night Mode</span>
          <label class="c2-switch">
            <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
        <div class="c2-toggle-row">
          <span>Language</span>
          <label class="c2-switch">
            <input type="checkbox" :checked="isArabic" @change="switchLang" />
            <span class="c2-switch-slider"></span>
          </label>
        </div>
      </div>
    </aside>

    <!-- Main Area -->
    <div class="c2-main">
      <!-- Top Header -->
      <header class="c2-header">
        <h1 class="c2-header-title">{{ pageTitle }}</h1>
        <div class="c2-header-right">
          <img src="@/assets/L3bnaIMG/notficationL3.png" class="c2-notif-icon" alt="Notifications" />
          <img src="@/assets/L3bnaIMG/ChatL3.png" class="c2-notif-icon" alt="Chat" />
          <div class="c2-user-pill">
            <img :src="userAvatar || defaultAvatar" alt="Avatar" class="c2-avatar" />
            <span class="c2-username">{{ displayName }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content Slot -->
      <div class="c2-body">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import '@/assets/styles/company2.css';

export default {
  name: 'Company2Layout',
  props: {
    pageTitle: {
      type: String,
      default: 'Company 2'
    }
  },
  data() {
    return {
      displayName: '',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      isArabic: false
    };
  },
  computed: {
    ...mapState(['isDarkMode', 'user'])
  },
  mounted() {
    this.isArabic = this.$i18n.locale === 'ar';
    const u = this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    if (u) {
      this.displayName = u.name || u.username || 'User';
      this.userAvatar = u.picture || '';
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale']),
    switchLang() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.$i18n.locale = newLocale;
      this.setLocale(newLocale);
      this.isArabic = !this.isArabic;
    }
  }
};
</script>
