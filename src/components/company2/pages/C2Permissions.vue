<template>
  <Company2Layout page-title="Permissions">
    <div class="c2-section-header">
      <h2 class="c2-section-title">Role-Based Access Control</h2>
      <button class="c2-btn c2-btn-primary" @click="saveAll">💾 Save Changes</button>
    </div>

    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table perm-table">
          <thead>
            <tr>
              <th style="width:200px">Module</th>
              <th v-for="role in roles" :key="role" class="role-col">{{ role }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="module in modules" :key="module.name">
              <td>
                <strong>{{ module.label }}</strong>
                <div style="font-size:0.72rem;color:var(--c2-text-muted)">{{ module.desc }}</div>
              </td>
              <td v-for="role in roles" :key="role">
                <div class="perm-checks">
                  <label v-for="action in actions" :key="action.key" class="perm-check">
                    <input
                      type="checkbox"
                      :checked="hasPermission(role, module.name, action.key)"
                      @change="togglePerm(role, module.name, action.key)"
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

    <p v-if="saved" style="color:#27ae60;text-align:center;margin-top:14px;font-weight:600">✓ Permissions saved successfully!</p>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Permissions',
  components: { Company2Layout },
  data() {
    return {
      saved: false,
      roles: ['Admin', 'Manager', 'Staff', 'Viewer'],
      actions: [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' }
      ],
      modules: [
        { name: 'merchants', label: 'Merchants', desc: 'Manage merchant accounts' },
        { name: 'bookings', label: 'Bookings', desc: 'View and update bookings' },
        { name: 'reviews', label: 'Reviews', desc: 'Moderate user reviews' },
        { name: 'settlement', label: 'Settlement', desc: 'Financial settlements' },
        { name: 'ads', label: 'Ads', desc: 'Ad campaigns' },
        { name: 'content', label: 'Content', desc: 'CMS pages' },
        { name: 'reports', label: 'Reports', desc: 'Analytics reports' },
        { name: 'settings', label: 'Settings', desc: 'System configuration' }
      ],
      // permissions[role][module] = Set of actions
      permissions: {
        Admin: {
          merchants: new Set(['view','create','edit','delete']),
          bookings: new Set(['view','create','edit','delete']),
          reviews: new Set(['view','create','edit','delete']),
          settlement: new Set(['view','create','edit','delete']),
          ads: new Set(['view','create','edit','delete']),
          content: new Set(['view','create','edit','delete']),
          reports: new Set(['view','create','edit','delete']),
          settings: new Set(['view','create','edit','delete'])
        },
        Manager: {
          merchants: new Set(['view','create','edit']),
          bookings: new Set(['view','create','edit']),
          reviews: new Set(['view','edit']),
          settlement: new Set(['view']),
          ads: new Set(['view','create','edit']),
          content: new Set(['view','create','edit']),
          reports: new Set(['view']),
          settings: new Set(['view'])
        },
        Staff: {
          merchants: new Set(['view']),
          bookings: new Set(['view','edit']),
          reviews: new Set(['view']),
          settlement: new Set([]),
          ads: new Set(['view']),
          content: new Set(['view']),
          reports: new Set([]),
          settings: new Set([])
        },
        Viewer: {
          merchants: new Set(['view']),
          bookings: new Set(['view']),
          reviews: new Set(['view']),
          settlement: new Set([]),
          ads: new Set(['view']),
          content: new Set(['view']),
          reports: new Set(['view']),
          settings: new Set([])
        }
      }
    };
  },
  methods: {
    hasPermission(role, module, action) {
      return this.permissions[role] && this.permissions[role][module] && this.permissions[role][module].has(action);
    },
    togglePerm(role, module, action) {
      const set = this.permissions[role][module];
      if (set.has(action)) set.delete(action);
      else set.add(action);
      // Force reactivity
      this.$set(this.permissions[role], module, new Set(set));
    },
    saveAll() {
      this.saved = true;
      setTimeout(() => (this.saved = false), 2500);
    }
  }
};
</script>

<style scoped>
.perm-table th.role-col { text-align: center; min-width: 130px; }
.perm-checks { display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
.perm-check { display: flex; align-items: center; gap: 5px; font-size: 0.75rem; cursor: pointer; user-select: none; }
.perm-check input { cursor: pointer; accent-color: #00aaff; }
</style>
