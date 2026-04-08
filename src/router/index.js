import Vue from 'vue';
import VueRouter from 'vue-router';
import Login from '../components/Login.vue';
import SignUp from '../components/SignUp.vue';
import Dashboard from '../components/Dashboard.vue';
import NotificationSettings from '../components/NotificationSettings.vue';
import Reports from '../components/Reports.vue';
import errorpage from '../components/errorpage.vue';
import fawaz from '../components/AddTestimonial.vue';
import ProfilePage from '../components/ProfilePage.vue';
import SettingsPage from '../components/SettingsPage.vue';
import InvoicesPage from '../components/InvoicesPage.vue';

Vue.use(VueRouter);

// Routes that require authentication
const protectedRoutes = [
  '/dashboard',
  '/notification-settings',
  '/reports',
  '/profile',
  '/settings',
  '/invoices',
  '/fawaz'
];

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/signup', component: SignUp },
  { path: '/fawaz', component: fawaz },
  { path: '/dashboard', component: Dashboard },
  { path: '/notification-settings', component: NotificationSettings },
  { path: '/reports', component: Reports },
  { path: '/errorpage', component: errorpage },
  { path: '/profile', component: ProfilePage },
  { path: '/settings', component: SettingsPage },
  { path: '/invoices', component: InvoicesPage },
];

const router = new VueRouter({
  mode: 'history',
  routes,
});

// Global navigation guard — redirect to login if not authenticated
router.beforeEach((to, from, next) => {
  const isProtected = protectedRoutes.some(route => to.path.startsWith(route));
  if (isProtected) {
    const user = localStorage.getItem('loggedInUser');
    if (!user) {
      next('/login');
      return;
    }
  }
  next();
});

export default router;
