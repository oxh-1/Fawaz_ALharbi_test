import Vue from 'vue';
import VueRouter from 'vue-router';
import Login from '../components/Login.vue';
import SignUp from '../components/SignUp.vue';
import Dashboard from '../components/Dashboard.vue';
import NotificationSettings from '../components/NotificationSettings.vue';
import Reports from '../components/Reports.vue';
import errorpage from '../components/errorpage.vue';

Vue.use(VueRouter);

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/signup', component: SignUp },
  { path: '/dashboard', component: Dashboard },
  { path: '/notification-settings', component: NotificationSettings },
  { path: '/reports', component: Reports },
  { path: '/errorpage', component: errorpage },
];

const router = new VueRouter({
  mode: 'history',
  routes,
});

export default router;
