<template>
  <div :class="['profile-page-root', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="profile-navbar">
      <div class="nav-left">
        <router-link to="/dashboard" class="brand-link">
          <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
          <span class="brand-name">Fawaz Platform</span>
        </router-link>
        <span class="nav-divider">/</span>
        <span class="nav-page-badge">👤 Account Profile</span>
      </div>

      <div class="nav-right">
        <router-link to="/dashboard" class="nav-pill-btn">🏠 Hub</router-link>
        <router-link to="/c2/home" class="nav-pill-btn">🏢 Company 2</router-link>
        <router-link to="/c3/stocks" class="nav-pill-btn">📈 Company 3</router-link>
        <router-link to="/c4/properties" class="nav-pill-btn">🏢 Company 4</router-link>
        <router-link to="/c5/academy" class="nav-pill-btn">🎓 Company 5</router-link>
        <router-link to="/settings" class="nav-pill-btn">⚙️ Settings</router-link>
        <router-link to="/notification-settings" class="nav-pill-btn">🔔 Alerts</router-link>

        <button class="nav-icon-btn" @click="toggleDarkMode" :title="'Toggle Dark Mode'">
          {{ isDarkMode ? '☀️' : '🌙' }}
        </button>
        <button class="nav-icon-btn" @click="toggleLanguage" :title="'Switch Language'">
          🌐 {{ isArabic ? 'EN' : 'عربي' }}
        </button>

        <div class="nav-user-pill">
          <img :src="profile.avatar || defaultAvatar" alt="Avatar" class="user-avatar-sm" />
          <span class="user-name-sm">{{ profile.name }}</span>
        </div>

        <button class="nav-logout-btn" @click="handleLogout" title="Logout">
          🚪 Logout
        </button>
      </div>
    </header>

    <!-- Main Container -->
    <main class="profile-main-container">
      <!-- Top Hero Card -->
      <div class="profile-hero-card">
        <div class="hero-left">
          <div class="avatar-upload-wrap">
            <img :src="profile.avatar || defaultAvatar" alt="Avatar" class="hero-avatar" />
            <label class="avatar-edit-badge" title="Change Avatar Image">
              📷
              <input type="file" @change="handleAvatarUpload" accept="image/*" style="display:none" />
            </label>
          </div>
          <div class="hero-meta">
            <div class="hero-name-row">
              <h1 class="hero-name">{{ profile.name }}</h1>
              <span class="badge-role">{{ isAdmin ? '👑 Super Admin' : '👤 Business Manager' }}</span>
              <span class="badge-verified">✓ Verified</span>
            </div>
            <p class="hero-email">{{ profile.email }} • Member since August 2024</p>
            <div class="hero-tags">
              <span class="hero-tag">🏢 Headquarters (Riyadh)</span>
              <span class="hero-tag">💼 Executive Management</span>
              <span class="hero-tag">🟢 Online Now</span>
            </div>
          </div>
        </div>

        <div class="hero-right">
          <div class="profile-completion-box">
            <div class="comp-header">
              <span class="comp-label">Profile Security Score</span>
              <span class="comp-val">95%</span>
            </div>
            <div class="comp-bar-bg">
              <div class="comp-bar-fill" style="width: 95%"></div>
            </div>
            <span class="comp-hint">Two-Factor Authentication & Email Verified</span>
          </div>
        </div>
      </div>

      <!-- Success Alert Banner -->
      <div v-if="saveSuccess" class="success-banner">
        ✅ Profile updated successfully! Your account information is synchronized.
      </div>
      <div v-if="passwordSuccess" class="success-banner">
        🔒 Password changed successfully! Please use your new password next time you sign in.
      </div>

      <!-- Profile Tabs Navigation -->
      <div class="profile-tabs-bar">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="['profile-tab-btn', { active: activeTab === tab.id }]"
          @click="activeTab = tab.id"
        >
          <span>{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- TAB 1: Personal Details & Contact -->
      <section v-if="activeTab === 'personal'" class="profile-card-pane">
        <div class="pane-header-row">
          <div>
            <h2 class="pane-title">👤 Personal Details & Contact Info</h2>
            <p class="pane-desc">Update your personal profile, job title, phone number, and public bio.</p>
          </div>
          <button class="save-profile-btn" @click="saveProfile" :disabled="isSaving">
            <span v-if="isSaving" class="btn-spinner"></span>
            <span v-else>💾 Save Profile Changes</span>
          </button>
        </div>

        <div class="form-grid-2">
          <div class="form-field">
            <label class="field-label">Full Name *</label>
            <input v-model="profile.name" class="form-input" placeholder="e.g. Fawaz Alharbi" />
          </div>
          <div class="form-field">
            <label class="field-label">Email Address (Read-only)</label>
            <input :value="profile.email" readonly class="form-input disabled" />
          </div>
          <div class="form-field">
            <label class="field-label">Phone Number *</label>
            <input v-model="profile.phone" class="form-input" placeholder="+966 50 123 4567" />
          </div>
          <div class="form-field">
            <label class="field-label">Job Title</label>
            <input v-model="profile.title" class="form-input" placeholder="Platform Director" />
          </div>
          <div class="form-field">
            <label class="field-label">Department / Branch</label>
            <input v-model="profile.department" class="form-input" placeholder="Operations & Technology" />
          </div>
          <div class="form-field">
            <label class="field-label">Preferred Contact Language</label>
            <select v-model="profile.preferredLang" class="form-select">
              <option value="ar">العربية (Arabic)</option>
              <option value="en">English</option>
            </select>
          </div>
          <div class="form-field full-width">
            <label class="field-label">Bio / Profile Summary</label>
            <textarea v-model="profile.bio" rows="3" class="form-input" placeholder="Brief professional overview..."></textarea>
          </div>
        </div>
      </section>

      <!-- TAB 2: Password & Authentication Security -->
      <section v-if="activeTab === 'security'" class="profile-card-pane">
        <div class="pane-header-row">
          <div>
            <h2 class="pane-title">🔒 Password & Authentication Security</h2>
            <p class="pane-desc">Manage account password, two-factor authentication, and login credentials.</p>
          </div>
        </div>

        <form @submit.prevent="updatePassword" class="password-form-wrap">
          <div v-if="passwordError" class="error-banner">
            ⚠️ {{ passwordError }}
          </div>

          <div class="form-field">
            <label class="field-label">Current Password *</label>
            <div class="pwd-input-wrap">
              <input :type="showCurrentPwd ? 'text' : 'password'" v-model="pwdForm.current" class="form-input" placeholder="Enter current password" required />
              <button type="button" class="pwd-eye-btn" @click="showCurrentPwd = !showCurrentPwd">
                {{ showCurrentPwd ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div class="form-grid-2" style="margin-top:14px">
            <div class="form-field">
              <label class="field-label">New Password *</label>
              <div class="pwd-input-wrap">
                <input :type="showNewPwd ? 'text' : 'password'" v-model="pwdForm.newPwd" class="form-input" placeholder="Minimum 8 characters" required />
                <button type="button" class="pwd-eye-btn" @click="showNewPwd = !showNewPwd">
                  {{ showNewPwd ? '🙈' : '👁️' }}
                </button>
              </div>
            </div>
            <div class="form-field">
              <label class="field-label">Confirm New Password *</label>
              <div class="pwd-input-wrap">
                <input :type="showConfirmPwd ? 'text' : 'password'" v-model="pwdForm.confirmPwd" class="form-input" placeholder="Repeat new password" required />
                <button type="button" class="pwd-eye-btn" @click="showConfirmPwd = !showConfirmPwd">
                  {{ showConfirmPwd ? '🙈' : '👁️' }}
                </button>
              </div>
            </div>
          </div>

          <div class="pwd-strength-bar-wrap" v-if="pwdForm.newPwd">
            <div class="strength-labels">
              <span>Password Complexity:</span>
              <span :class="['strength-tag', pwdStrength.class]">{{ pwdStrength.label }}</span>
            </div>
            <div class="strength-track">
              <div :class="['strength-progress', pwdStrength.class]" :style="{ width: pwdStrength.pct + '%' }"></div>
            </div>
          </div>

          <button type="submit" class="save-profile-btn" style="margin-top:20px" :disabled="isUpdatingPwd">
            <span v-if="isUpdatingPwd" class="btn-spinner"></span>
            <span v-else>🔐 Update Password</span>
          </button>
        </form>
      </section>

      <!-- TAB 3: Permissions & Roles Matrix -->
      <section v-if="activeTab === 'permissions'" class="profile-card-pane">
        <div class="pane-header-row">
          <div>
            <h2 class="pane-title">🛡️ Assigned Roles & System Permissions</h2>
            <p class="pane-desc">Enterprise Role-Based Access Control (RBAC) scopes granted to your account.</p>
          </div>
          <span class="badge-role">Super Admin Scope</span>
        </div>

        <div class="permissions-grid">
          <div v-for="perm in permissions" :key="perm.key" class="perm-card">
            <div class="perm-icon-wrap">✓</div>
            <div class="perm-info">
              <span class="perm-name">{{ perm.name }}</span>
              <span class="perm-desc">{{ perm.desc }}</span>
              <code class="perm-scope">{{ perm.scope }}</code>
            </div>
          </div>
        </div>
      </section>

      <!-- TAB 4: Activity & Session History -->
      <section v-if="activeTab === 'activity'" class="profile-card-pane">
        <div class="pane-header-row">
          <div>
            <h2 class="pane-title">📜 Security Audit Log & Recent Activity</h2>
            <p class="pane-desc">Recent authentication timestamps, device locations, and actions performed.</p>
          </div>
          <button class="logout-sessions-btn" @click="handleRevokeAll">
            🔒 Revoke Other Sessions
          </button>
        </div>

        <div class="activity-timeline">
          <div v-for="act in activityLogs" :key="act.id" class="activity-row">
            <div class="act-icon-wrap">{{ act.icon }}</div>
            <div class="act-details">
              <div class="act-title-row">
                <span class="act-action">{{ act.action }}</span>
                <span class="act-time">{{ act.time }}</span>
              </div>
              <span class="act-meta">Device: {{ act.device }} • IP: {{ act.ip }} • Location: {{ act.location }}</span>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'ProfilePage',
  data() {
    return {
      activeTab: 'personal',
      isSaving: false,
      saveSuccess: false,
      isUpdatingPwd: false,
      passwordSuccess: false,
      passwordError: '',
      showCurrentPwd: false,
      showNewPwd: false,
      showConfirmPwd: false,
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      isArabic: false,
      tabs: [
        { id: 'personal',    icon: '👤', label: 'Personal Details' },
        { id: 'security',    icon: '🔒', label: 'Password & Security' },
        { id: 'permissions', icon: '🛡️', label: 'Roles & Permissions' },
        { id: 'activity',    icon: '📜', label: 'Activity Logs' },
      ],
      profile: {
        name: 'Fawaz Alharbi',
        email: 'fawazalharbi04@gmail.com',
        phone: '+966 50 123 4567',
        title: 'Platform Director & Administrator',
        department: 'Executive Operations',
        preferredLang: 'ar',
        bio: 'Leading digital operations, merchant integrations, and automated booking systems at Fawaz Platform.',
        avatar: ''
      },
      pwdForm: {
        current: '',
        newPwd: '',
        confirmPwd: ''
      },
      permissions: [
        { key: 'p1', name: 'Super Admin Full Control', desc: 'Manage system settings, tenant routing, and databases', scope: 'system:*' },
        { key: 'p2', name: 'Merchant Management', desc: 'Create, approve, and configure merchants and categories', scope: 'merchants:write' },
        { key: 'p3', name: 'Booking & Appointment Operations', desc: 'Manage customer bookings, calendars, and slots', scope: 'bookings:manage' },
        { key: 'p4', name: 'Financial Settlements & Invoices', desc: 'Execute payouts, export VAT reports, audit invoices', scope: 'finance:settle' },
        { key: 'p5', name: 'AI & Automated Assistant Support', desc: 'Configure AI knowledge parameters and chat logs', scope: 'ai:support' },
        { key: 'p6', name: 'Customer Directory & VIP Tiers', desc: 'View customer lifetime value and adjust status', scope: 'customers:all' }
      ],
      activityLogs: [
        { id: 1, icon: '🟢', action: 'Current Active Session', time: 'Active now', device: 'Chrome on Windows 11', ip: '127.0.0.1', location: 'Riyadh, Saudi Arabia' },
        { id: 2, icon: '🔑', action: 'Successful Google SSO Login', time: 'Today at 5:15 PM', device: 'Chrome on Windows 11', ip: '127.0.0.1', location: 'Riyadh, Saudi Arabia' },
        { id: 3, icon: '⚙️', action: 'Updated Notification Preferences', time: 'Today at 5:29 PM', device: 'Web Dashboard', ip: '127.0.0.1', location: 'Riyadh, Saudi Arabia' },
        { id: 4, icon: '📊', action: 'Exported Financial Audit Log', time: 'Yesterday at 3:42 PM', device: 'Chrome Desktop', ip: '127.0.0.1', location: 'Riyadh, Saudi Arabia' },
      ]
    };
  },
  computed: {
    ...mapState({
      user:       state => state.auth ? state.auth.user : null,
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
      locale:     state => state.settings ? state.settings.locale : 'en',
    }),
    currentUser() {
      return this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    },
    isAdmin() {
      if (!this.currentUser) return true;
      return this.currentUser.is_super_admin || this.currentUser.email === 'admin@company2.com' || this.currentUser.email === 'fawazalharbi04@gmail.com';
    },
    pwdStrength() {
      const p = this.pwdForm.newPwd;
      if (!p) return { label: 'Empty', class: 'none', pct: 0 };
      if (p.length < 6) return { label: 'Weak', class: 'weak', pct: 30 };
      if (p.length >= 8 && /[A-Z]/.test(p) && /[0-9]/.test(p)) return { label: 'Strong & Secure', class: 'strong', pct: 100 };
      return { label: 'Medium', class: 'medium', pct: 65 };
    }
  },
  mounted() {
    this.isArabic = this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
    document.body.classList.toggle('rtl', this.isArabic);

    if (this.currentUser) {
      this.profile.name = this.currentUser.name || 'Fawaz Alharbi';
      this.profile.email = this.currentUser.email || 'fawazalharbi04@gmail.com';
      this.profile.avatar = this.currentUser.picture || this.currentUser.avatar || '';
    }

    // Load stored custom profile
    const saved = localStorage.getItem('fawaz_user_profile');
    if (saved) {
      try {
        this.profile = { ...this.profile, ...JSON.parse(saved) };
      } catch (e) {
        console.warn('Failed to parse profile', e);
      }
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout', 'setUser']),

    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.setLanguage(newLocale);
    },

    setLanguage(locale) {
      this.isArabic = locale === 'ar';
      this.$i18n.locale = locale;
      this.setLocale(locale);
      document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
      document.body.classList.toggle('rtl', this.isArabic);
    },

    handleAvatarUpload(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.profile.avatar = e.target.result;
          this.saveProfile();
        };
        reader.readAsDataURL(file);
      }
    },

    saveProfile() {
      this.isSaving = true;
      this.saveSuccess = false;

      // Update local storage and Vuex user
      localStorage.setItem('fawaz_user_profile', JSON.stringify(this.profile));

      const updatedUser = {
        ...(this.currentUser || {}),
        name: this.profile.name,
        picture: this.profile.avatar,
        phone: this.profile.phone,
        bio: this.profile.bio
      };
      localStorage.setItem('loggedInUser', JSON.stringify(updatedUser));
      this.setUser(updatedUser);

      setTimeout(() => {
        this.isSaving = false;
        this.saveSuccess = true;
        setTimeout(() => { this.saveSuccess = false; }, 3000);
      }, 400);
    },

    updatePassword() {
      this.passwordError = '';
      this.passwordSuccess = false;

      if (this.pwdForm.newPwd !== this.pwdForm.confirmPwd) {
        this.passwordError = 'New password and confirmation do not match.';
        return;
      }
      if (this.pwdForm.newPwd.length < 8) {
        this.passwordError = 'Password must be at least 8 characters long.';
        return;
      }

      this.isUpdatingPwd = true;
      setTimeout(() => {
        this.isUpdatingPwd = false;
        this.passwordSuccess = true;
        this.pwdForm = { current: '', newPwd: '', confirmPwd: '' };
        setTimeout(() => { this.passwordSuccess = false; }, 4000);
      }, 500);
    },

    async handleRevokeAll() {
      alert('All other active sessions on other devices have been revoked.');
    },

    async handleLogout() {
      try {
        await this.logout();
      } catch (e) {
        console.warn('Logout action handled', e);
      } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('loggedInUser');
        this.$router.push('/login').catch(() => {
          window.location.href = '/login';
        });
      }
    }
  }
};
</script>

<style scoped>
.profile-page-root {
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.profile-page-root.dark {
  background: #0f172a;
  color: #f8fafc;
}
.profile-page-root.rtl {
  direction: rtl;
}

/* Navbar */
.profile-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.dark .profile-navbar {
  background: #181824;
  border-bottom-color: #2d3748;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.brand-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
}
.brand-logo {
  height: 32px;
  width: auto;
  object-fit: contain;
}
.brand-name {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0284c7;
}
.nav-divider {
  color: #94a3b8;
}
.nav-page-badge {
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
}
.dark .nav-page-badge {
  color: #94a3b8;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.nav-pill-btn {
  padding: 6px 12px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 700;
  transition: all 0.2s;
}
.dark .nav-pill-btn {
  background: #282a36;
  color: #e2e8f0;
}
.nav-pill-btn:hover {
  background: #0284c7;
  color: #ffffff;
}

.nav-icon-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 50%;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}
.dark .nav-icon-btn {
  background: #282a36;
  border-color: #475569;
  color: #ffffff;
}

.nav-user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: 30px;
  background: #f1f5f9;
}
.dark .nav-user-pill {
  background: #282a36;
}
.user-avatar-sm {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  object-fit: cover;
}
.user-name-sm {
  font-size: 0.8rem;
  font-weight: 700;
}

.nav-logout-btn {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.nav-logout-btn:hover {
  background: #ef4444;
  color: #ffffff;
}

/* Main Body */
.profile-main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px 60px;
}

/* Hero Card */
.profile-hero-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 24px;
}
.dark .profile-hero-card {
  background: #181824;
  border-color: #2d3748;
}

.hero-left {
  display: flex;
  align-items: center;
  gap: 20px;
}
@media (max-width: 700px) {
  .hero-left {
    flex-direction: column;
    text-align: center;
  }
}

.avatar-upload-wrap {
  position: relative;
}
.hero-avatar {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #0284c7;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
}
.avatar-edit-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  background: #0284c7;
  color: #ffffff;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.85rem;
  border: 2px solid #ffffff;
  transition: transform 0.2s;
}
.avatar-edit-badge:hover {
  transform: scale(1.1);
}

.hero-name-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.hero-name {
  font-size: 1.5rem;
  font-weight: 900;
  margin: 0;
  letter-spacing: -0.3px;
}
.badge-role {
  background: rgba(2, 132, 199, 0.15);
  color: #0284c7;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 20px;
}
.badge-verified {
  background: #dcfce7;
  color: #166534;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 20px;
}
.hero-email {
  font-size: 0.85rem;
  color: #64748b;
  margin: 4px 0 10px 0;
}
.dark .hero-email {
  color: #94a3b8;
}

.hero-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.hero-tag {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 8px;
}
.dark .hero-tag {
  background: #282a36;
  color: #cbd5e1;
}

/* Security Meter */
.profile-completion-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px 20px;
  min-width: 240px;
}
.dark .profile-completion-box {
  background: #1e1e2e;
  border-color: #2d3748;
}
.comp-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  font-weight: 800;
  margin-bottom: 8px;
}
.comp-bar-bg {
  height: 8px;
  background: #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 6px;
}
.dark .comp-bar-bg {
  background: #334155;
}
.comp-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #0284c7);
  border-radius: 10px;
}
.comp-hint {
  font-size: 0.68rem;
  color: #64748b;
}
.dark .comp-hint {
  color: #94a3b8;
}

/* Success & Error Banners */
.success-banner {
  background: #dcfce7;
  color: #166534;
  padding: 12px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 0.875rem;
  font-weight: 700;
}
.error-banner {
  background: #fee2e2;
  color: #991b1b;
  padding: 12px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 0.875rem;
  font-weight: 700;
}

/* Tabs Bar */
.profile-tabs-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  overflow-x: auto;
}
.profile-tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.dark .profile-tab-btn {
  background: #181824;
  border-color: #2d3748;
  color: #cbd5e1;
}
.profile-tab-btn.active {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
}

/* Card Pane */
.profile-card-pane {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.dark .profile-card-pane {
  background: #181824;
  border-color: #2d3748;
}

.pane-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.pane-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0 0 4px 0;
}
.pane-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
}
.dark .pane-desc {
  color: #94a3b8;
}

.save-profile-btn {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  padding: 10px 22px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
  transition: all 0.2s;
}
.save-profile-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
}

/* Form Controls */
.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
@media (max-width: 750px) {
  .form-grid-2 {
    grid-template-columns: 1fr;
  }
}
.full-width {
  grid-column: 1 / -1;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
}
.dark .field-label {
  color: #cbd5e1;
}

.form-input, .form-select {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
}
.dark .form-input, .dark .form-select {
  background: #1e1e2e;
  border-color: #475569;
}
.form-input:focus, .form-select:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
.form-input.disabled {
  background: #f1f5f9;
  cursor: not-allowed;
  opacity: 0.8;
}
.dark .form-input.disabled {
  background: #282a36;
}

/* Password Form */
.password-form-wrap {
  max-width: 650px;
}
.pwd-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.pwd-input-wrap .form-input {
  width: 100%;
  padding-right: 40px;
}
.rtl .pwd-input-wrap .form-input {
  padding-right: 14px;
  padding-left: 40px;
}
.pwd-eye-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}
.rtl .pwd-eye-btn {
  right: auto;
  left: 10px;
}

/* Password Strength Bar */
.pwd-strength-bar-wrap {
  margin-top: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 14px;
}
.dark .pwd-strength-bar-wrap {
  background: #1e1e2e;
  border-color: #2d3748;
}
.strength-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 6px;
}
.strength-tag.weak { color: #ef4444; }
.strength-tag.medium { color: #f59e0b; }
.strength-tag.strong { color: #10b981; }

.strength-track {
  height: 6px;
  background: #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}
.dark .strength-track {
  background: #334155;
}
.strength-progress {
  height: 100%;
  transition: width 0.3s;
}
.strength-progress.weak { background: #ef4444; }
.strength-progress.medium { background: #f59e0b; }
.strength-progress.strong { background: #10b981; }

/* Permissions Grid */
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
}
.perm-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px;
}
.dark .perm-card {
  background: #1e1e2e;
  border-color: #2d3748;
}
.perm-icon-wrap {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #dcfce7;
  color: #166534;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.8rem;
  flex-shrink: 0;
}
.perm-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.perm-name {
  font-size: 0.875rem;
  font-weight: 700;
}
.perm-desc {
  font-size: 0.75rem;
  color: #64748b;
}
.dark .perm-desc {
  color: #94a3b8;
}
.perm-scope {
  font-size: 0.7rem;
  background: rgba(2, 132, 199, 0.1);
  color: #0284c7;
  padding: 2px 6px;
  border-radius: 4px;
  width: fit-content;
  margin-top: 4px;
}

/* Activity Logs Timeline */
.logout-sessions-btn {
  background: none;
  border: 1px solid #ef4444;
  color: #ef4444;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.logout-sessions-btn:hover {
  background: #ef4444;
  color: #ffffff;
}

.activity-timeline {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.activity-row {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
}
.dark .activity-row {
  background: #1e1e2e;
  border-color: #2d3748;
}
.act-icon-wrap {
  font-size: 1.1rem;
}
.act-details {
  display: flex;
  flex-direction: column;
  flex: 1;
}
.act-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.act-action {
  font-size: 0.875rem;
  font-weight: 700;
}
.act-time {
  font-size: 0.75rem;
  color: #94a3b8;
}
.act-meta {
  font-size: 0.75rem;
  color: #64748b;
}
.dark .act-meta {
  color: #94a3b8;
}

/* Spinner */
.btn-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
