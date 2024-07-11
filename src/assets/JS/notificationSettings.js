// notificationSettings.js

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
