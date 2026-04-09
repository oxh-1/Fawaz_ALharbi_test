import Vue from 'vue';
import VueRouter from 'vue-router';

// ── Gittax pages ──────────────────────────────────────────────────────────────
import Login from '../components/Login.vue';
import SignUp from '../components/SignUp.vue';
import Dashboard from '../components/Dashboard.vue';
import NotificationSettings from '../components/NotificationSettings.vue';
import OldReports from '../components/Reports.vue'; // "Company 2" entry point
import errorpage from '../components/errorpage.vue';
import fawaz from '../components/AddTestimonial.vue';
import ProfilePage from '../components/ProfilePage.vue';
import SettingsPage from '../components/SettingsPage.vue';
import InvoicesPage from '../components/InvoicesPage.vue';

// ── Company 2 pages (lazy-loaded for performance) ─────────────────────────────
const C2Home        = () => import('../components/company2/pages/C2Home.vue');
const C2Merchant    = () => import('../components/company2/pages/C2Merchant.vue');
const C2Categories  = () => import('../components/company2/pages/C2Categories.vue');
const C2Services    = () => import('../components/company2/pages/C2Services.vue');
const C2Booking     = () => import('../components/company2/pages/C2Booking.vue');
const C2Reviews     = () => import('../components/company2/pages/C2Reviews.vue');
const C2Contact     = () => import('../components/company2/pages/C2Contact.vue');
const C2Pricing     = () => import('../components/company2/pages/C2Pricing.vue');
const C2Ads         = () => import('../components/company2/pages/C2Ads.vue');
const C2Content     = () => import('../components/company2/pages/C2Content.vue');
const C2Settlement  = () => import('../components/company2/pages/C2Settlement.vue');
const C2Reports     = () => import('../components/company2/pages/C2Reports.vue');
const C2Permissions = () => import('../components/company2/pages/C2Permissions.vue');
const C2Settings    = () => import('../components/company2/pages/C2Settings.vue');

Vue.use(VueRouter);

// ── Domain Validation ─────────────────────────────────────────────────────────
// Add your production domain(s) here. localhost is always valid for dev.
const VALID_DOMAINS = [
  'localhost',
  '127.0.0.1',
  // Add production domains below:
  // 'yourdomain.com',
  // 'www.yourdomain.com',
  // 'app.yourdomain.com',
];

function isDomainValid() {
  const host = window.location.hostname;
  return VALID_DOMAINS.some(domain => host === domain || host.endsWith('.' + domain));
}

// ── Route Definitions ─────────────────────────────────────────────────────────
// Routes that redirect to /login if user is not authenticated
const protectedRoutes = [
  '/dashboard',
  '/notification-settings',
  '/reports',
  '/profile',
  '/settings',
  '/invoices',
  '/fawaz',
  '/c2', // all Company 2 routes
];

const routes = [
  // ── Public routes ──
  { path: '/',        redirect: '/login' },
  { path: '/login',   component: Login,   meta: { title: 'Login — Fawaz Platform' } },
  { path: '/signup',  component: SignUp,  meta: { title: 'Sign Up — Fawaz Platform' } },
  { path: '/404',     component: errorpage, meta: { title: '404 — Page Not Found' } },

  // ── Gittax protected routes ──
  { path: '/fawaz',                  component: fawaz,                meta: { title: 'Add Testimonial' } },
  { path: '/dashboard',              component: Dashboard,            meta: { title: 'Dashboard' } },
  { path: '/notification-settings', component: NotificationSettings, meta: { title: 'Notification Settings' } },
  { path: '/reports',               component: OldReports,           meta: { title: 'Company 2 — Entry' } },
  { path: '/profile',               component: ProfilePage,          meta: { title: 'My Profile' } },
  { path: '/settings',              component: SettingsPage,         meta: { title: 'Settings' } },
  { path: '/invoices',              component: InvoicesPage,         meta: { title: 'Invoices' } },
  { path: '/errorpage',             component: errorpage,            meta: { title: 'Error' } },

  // ── Company 2 routes ──
  { path: '/c2',              redirect: '/c2/home' },
  { path: '/c2/home',         component: C2Home,        meta: { title: 'Home — Company 2' } },
  { path: '/c2/merchant',     component: C2Merchant,    meta: { title: 'Merchants — Company 2' } },
  { path: '/c2/categories',   component: C2Categories,  meta: { title: 'Categories — Company 2' } },
  { path: '/c2/services',     component: C2Services,    meta: { title: 'Services — Company 2' } },
  { path: '/c2/booking',      component: C2Booking,     meta: { title: 'Booking — Company 2' } },
  { path: '/c2/reviews',      component: C2Reviews,     meta: { title: 'Reviews — Company 2' } },
  { path: '/c2/contact',      component: C2Contact,     meta: { title: 'Contact Us — Company 2' } },
  { path: '/c2/pricing',      component: C2Pricing,     meta: { title: 'Pricing — Company 2' } },
  { path: '/c2/ads',          component: C2Ads,         meta: { title: 'Ads — Company 2' } },
  { path: '/c2/content',      component: C2Content,     meta: { title: 'Content — Company 2' } },
  { path: '/c2/settlement',   component: C2Settlement,  meta: { title: 'Settlement — Company 2' } },
  { path: '/c2/reports',      component: C2Reports,     meta: { title: 'Reports — Company 2' } },
  { path: '/c2/permissions',  component: C2Permissions, meta: { title: 'Permissions — Company 2' } },
  { path: '/c2/settings',     component: C2Settings,    meta: { title: 'Settings — Company 2' } },

  // ── Catch-all: any unknown route → 404 ──
  { path: '*', redirect: '/404' },
];

const router = new VueRouter({
  mode: 'history',
  routes,
  scrollBehavior() {
    return { x: 0, y: 0 }; // always scroll to top on navigation
  }
});

// ── Global Navigation Guard ───────────────────────────────────────────────────
router.beforeEach((to, from, next) => {
  // 1. Domain validation (skip for the 404 page itself to avoid infinite loop)
  if (to.path !== '/404' && !isDomainValid()) {
    const badDomain = window.location.hostname;
    console.warn(`[Router] Invalid domain detected: "${badDomain}" → redirecting to /404`);
    next('/404');
    return;
  }

  // 2. Authentication guard for protected routes
  const isProtected = protectedRoutes.some(route => to.path.startsWith(route));
  if (isProtected) {
    const user = localStorage.getItem('loggedInUser');
    if (!user) {
      console.info(`[Router] Unauthenticated access to "${to.path}" → redirecting to /login`);
      next('/login');
      return;
    }
  }

  // 3. Update document title
  if (to.meta && to.meta.title) {
    document.title = to.meta.title;
  }

  next();
});

export default router;
