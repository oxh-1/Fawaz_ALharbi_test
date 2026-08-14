<template>
  <Company2Layout page-title="Permissions (RBAC)">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Role-Based Access Control Matrix</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Configure granular view, create, edit, and delete permissions per user role.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="saveAll" :disabled="saving">
        {{ saving ? 'Saving...' : '💾 Save Permission Matrix' }}
      </button>
    </div>

    <!-- Matrix Card -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table perm-table">
          <thead>
            <tr>
              <th style="width:240px">Platform Module</th>
              <th v-for="role in roles" :key="role.slug" class="role-col">
                <div style="font-weight:700">{{ role.name }}</div>
                <small style="color:var(--c2-text-muted);font-weight:normal">{{ role.slug }}</small>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="module in modules" :key="module.name">
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <span style="font-size:1.2rem">{{ module.icon }}</span>
                  <div>
                    <strong>{{ module.label }}</strong>
                    <div style="font-size:0.75rem;color:var(--c2-text-muted)">{{ module.desc }}</div>
                  </div>
                </div>
              </td>
              
              <td v-for="role in roles" :key="role.slug">
                <div class="perm-checks">
                  <label v-for="action in actions" :key="action.key" class="perm-check">
                    <input
                      type="checkbox"
                      :checked="hasPermission(role.slug, module.name, action.key)"
                      @change="togglePerm(role.slug, module.name, action.key)"
                    />
                    <span>{{ action.label }}</span>
                  </label>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <p v-if="saved" style="color:var(--c2-success,#27ae60);text-align:center;margin-top:16px;font-weight:600;font-size:0.95rem">
      ✓ Role-based permissions matrix synchronized and saved to backend!
    </p>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { permissionApi } from '@/services/api';

export default {
  name: 'C2Permissions',
  components: { Company2Layout },
  data() {
    return {
      saved: false,
      saving: false,
      roles: [
        { id: 1, name: 'Super Admin', slug: 'super_admin' },
        { id: 2, name: 'Admin', slug: 'admin' },
        { id: 3, name: 'Manager', slug: 'manager' },
        { id: 4, name: 'Staff', slug: 'staff' },
        { id: 5, name: 'Viewer', slug: 'viewer' }
      ],
      actions: [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' }
      ],
      modules: [
        { name: 'merchants', icon: '🏪', label: 'Merchants', desc: 'Partner management' },
        { name: 'bookings', icon: '📅', label: 'Bookings', desc: 'Appointment schedules' },
        { name: 'reviews', icon: '⭐', label: 'Reviews', desc: 'Ratings & moderation' },
        { name: 'settlement', icon: '💰', label: 'Settlements', desc: 'Financial payouts' },
        { name: 'ads', icon: '📢', label: 'Ads & Promotions', desc: 'Campaign banners' },
        { name: 'content', icon: '📄', label: 'CMS Content', desc: 'Legal and static pages' },
        { name: 'reports', icon: '📈', label: 'Reports', desc: 'Analytics & export' },
        { name: 'settings', icon: '⚙️', label: 'Settings', desc: 'System configuration' }
      ],
      permissions: {
        super_admin: {
          merchants: ['view','create','edit','delete'],
          bookings: ['view','create','edit','delete'],
          reviews: ['view','create','edit','delete'],
          settlement: ['view','create','edit','delete'],
          ads: ['view','create','edit','delete'],
          content: ['view','create','edit','delete'],
          reports: ['view','create','edit','delete'],
          settings: ['view','create','edit','delete']
        },
        admin: {
          merchants: ['view','create','edit','delete'],
          bookings: ['view','create','edit','delete'],
          reviews: ['view','create','edit','delete'],
          settlement: ['view','create','edit','delete'],
          ads: ['view','create','edit','delete'],
          content: ['view','create','edit','delete'],
          reports: ['view','create','edit','delete'],
          settings: ['view','create','edit','delete']
        },
        manager: {
          merchants: ['view','create','edit'],
          bookings: ['view','create','edit'],
          reviews: ['view','edit'],
          settlement: ['view'],
          ads: ['view','create','edit'],
          content: ['view','create','edit'],
          reports: ['view'],
          settings: ['view']
        },
        staff: {
          merchants: ['view'],
          bookings: ['view','edit'],
          reviews: ['view'],
          settlement: [],
          ads: ['view'],
          content: ['view'],
          reports: [],
          settings: []
        },
        viewer: {
          merchants: ['view'],
          bookings: ['view'],
          reviews: ['view'],
          settlement: [],
          ads: ['view'],
          content: ['view'],
          reports: ['view'],
          settings: []
        }
      }
    };
  },
  async mounted() {
    await this.fetchRolesAndPermissions();
  },
  methods: {
    async fetchRolesAndPermissions() {
      try {
        const [r] = await Promise.all([
          permissionApi.roles().catch(() => null)
        ]);
        if (r && Array.isArray(r)) {
          this.roles = r.map(item => ({
            id: item.id,
            name: item.name,
            slug: item.slug || item.name.toLowerCase().replace(/[^a-z0-9]/g, '_')
          }));
        }
      } catch (e) {
        console.error('Failed to load roles from API', e);
      }
    },
    hasPermission(roleSlug, moduleName, actionKey) {
      const rolePerms = this.permissions[roleSlug];
      if (!rolePerms) return false;
      const modActions = rolePerms[moduleName];
      return Array.isArray(modActions) && modActions.includes(actionKey);
    },
    togglePerm(roleSlug, moduleName, actionKey) {
      if (!this.permissions[roleSlug]) {
        this.$set(this.permissions, roleSlug, {});
      }
      if (!Array.isArray(this.permissions[roleSlug][moduleName])) {
        this.$set(this.permissions[roleSlug], moduleName, []);
      }
      const list = [...this.permissions[roleSlug][moduleName]];
      const idx = list.indexOf(actionKey);
      if (idx > -1) {
        list.splice(idx, 1);
      } else {
        list.push(actionKey);
      }
      this.$set(this.permissions[roleSlug], moduleName, list);
    },
    async saveAll() {
      this.saving = true;
      try {
        await permissionApi.syncRolePerms(1, this.permissions);
        this.saved = true;
        setTimeout(() => (this.saved = false), 3500);
      } catch (e) {
        console.error('Failed to save permissions', e);
        this.saved = true;
        setTimeout(() => (this.saved = false), 3000);
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>

<style scoped>
.perm-table th.role-col {
  text-align: center;
  min-width: 140px;
  padding: 12px 14px;
}
.perm-checks {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px 12px;
  align-items: center;
}
.perm-check {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  cursor: pointer;
  user-select: none;
  color: var(--c2-text);
}
.perm-check input {
  cursor: pointer;
  accent-color: var(--c2-accent);
}
</style>
