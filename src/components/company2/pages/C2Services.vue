<template>
  <Company2Layout page-title="Services Link">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Service Catalog & Links</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Connect bookable services with partner merchants and categories.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Link New Service</button>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search service, merchant..." />
        <select v-model="categoryFilter" class="c2-select">
          <option value="">All Categories</option>
          <option v-for="c in categoriesList" :key="c" :value="c">{{ c }}</option>
        </select>
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Statuses</option>
          <option value="active">Active Only</option>
          <option value="inactive">Inactive Only</option>
        </select>
      </div>
      <span style="font-size:0.85rem;color:var(--c2-text-muted)">{{ filtered.length }} services</span>
    </div>

    <!-- Table -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr>
              <th>Service Name</th>
              <th>Category</th>
              <th>Merchant</th>
              <th>Tags</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="srv in filtered" :key="srv.id">
              <td>
                <div style="font-weight:700;color:var(--c2-text)">{{ srv.name }}</div>
                <small style="color:var(--c2-text-muted)">ID #{{ srv.id }}</small>
              </td>
              <td>
                <span class="c2-badge" style="background:rgba(0,170,255,0.1);color:var(--c2-accent)">
                  {{ srv.category || 'General' }}
                </span>
              </td>
              <td>
                <strong>{{ srv.merchant || 'General Partner' }}</strong>
              </td>
              <td>
                <span v-for="tag in srv.tags" :key="tag" class="tag-chip">{{ tag }}</span>
                <span v-if="!srv.tags || srv.tags.length === 0" style="color:var(--c2-text-muted);font-size:0.8rem">-</span>
              </td>
              <td>
                <label class="c2-switch" title="Toggle Active">
                  <input type="checkbox" :checked="srv.active" @change="toggleService(srv)" />
                  <span class="c2-switch-slider"></span>
                </label>
              </td>
              <td>
                <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(srv)" style="margin-right:6px">Edit</button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteService(srv.id)">Remove</button>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="6" style="text-align:center;padding:40px;color:var(--c2-text-muted)">
                No services found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editing ? 'Edit Service' : 'Link New Service' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        
        <div class="c2-form-group">
          <label class="c2-form-label">Service Name *</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. VIP Car Detailing" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Category *</label>
          <select v-model="form.category" class="c2-form-select">
            <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Merchant Partner *</label>
          <select v-model="form.merchant" class="c2-form-select">
            <option v-for="m in availableMerchants" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Tags (comma-separated)</label>
          <input v-model="tagsInput" class="c2-form-input" placeholder="e.g. premium, featured, popular" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Active Status</label>
          <select v-model="form.active" class="c2-form-select">
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveService">{{ editing ? 'Update Service' : 'Save Service' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { serviceApi, categoryApi, merchantApi } from '@/services/api';

export default {
  name: 'C2Services',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      categoryFilter: '',
      statusFilter: '',
      showModal: false,
      editing: null,
      tagsInput: '',
      form: { name: '', category: 'Retail', merchant: 'Acme Store', tags: [], active: true },
      services: [],
      availableCategories: ['Food & Beverage', 'Retail', 'Technology', 'Healthcare', 'Beauty & Wellness', 'Sports & Fitness', 'Education', 'Automotive', 'Services'],
      availableMerchants: ['Acme Store', 'Beta Eats', 'Gamma Tech', 'Delta Health', 'Epsilon Services', 'Zeta Fashion']
    };
  },
  async mounted() {
    await this.fetchData();
    this.fetchOptions();
  },
  computed: {
    categoriesList() {
      const set = new Set(this.services.map(s => s.category).filter(Boolean));
      return Array.from(set);
    },
    filtered() {
      return this.services.filter(s => {
        const q = this.search.toLowerCase();
        const matchQuery = !q || (s.name && s.name.toLowerCase().includes(q)) || (s.merchant && s.merchant.toLowerCase().includes(q));
        const matchCategory = !this.categoryFilter || s.category === this.categoryFilter;
        const matchStatus = !this.statusFilter || (this.statusFilter === 'active' ? s.active : !s.active);
        return matchQuery && matchCategory && matchStatus;
      });
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await serviceApi.list();
        const list = Array.isArray(res) ? res : (res.data || []);
        this.services = list.map(s => {
          let tags = s.tags;
          if (typeof tags === 'string') {
            try { tags = JSON.parse(tags); } catch { tags = tags ? tags.split(',') : []; }
          }
          return {
            ...s,
            tags: Array.isArray(tags) ? tags : [],
            active: Boolean(s.active)
          };
        });
      } catch (e) {
        console.error('Failed to load services', e);
      }
    },
    async fetchOptions() {
      try {
        const [cats, merchants] = await Promise.all([
          categoryApi.list().catch(() => []),
          merchantApi.list().catch(() => []),
        ]);
        const catArr = Array.isArray(cats) ? cats : (cats.data || []);
        const mArr = Array.isArray(merchants) ? merchants : (merchants.data || []);
        if (catArr.length > 0) this.availableCategories = catArr.map(c => c.name);
        if (mArr.length > 0) this.availableMerchants = mArr.map(m => m.name);
      } catch (e) {
        console.error(e);
      }
    },
    openModal(s = null) {
      this.editing = s;
      if (s) {
        this.form = { ...s };
        this.tagsInput = Array.isArray(s.tags) ? s.tags.join(', ') : '';
      } else {
        this.form = {
          name: '',
          category: this.availableCategories[0] || 'Retail',
          merchant: this.availableMerchants[0] || 'Acme Store',
          tags: [],
          active: true
        };
        this.tagsInput = '';
      }
      this.showModal = true;
    },
    async saveService() {
      if (!this.form.name.trim()) {
        alert('Please enter a service name.');
        return;
      }
      this.form.tags = this.tagsInput.split(',').map(t => t.trim()).filter(Boolean);
      try {
        if (this.editing) {
          await serviceApi.update(this.editing.id, this.form);
        } else {
          await serviceApi.create(this.form);
        }
        await this.fetchData();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to save service', e);
      }
    },
    async deleteService(id) {
      if (confirm('Are you sure you want to remove this service link?')) {
        try {
          await serviceApi.delete(id);
          await this.fetchData();
        } catch (e) {
          console.error('Failed to delete service', e);
        }
      }
    },
    async toggleService(srv) {
      try {
        srv.active = !srv.active;
        await serviceApi.toggle(srv.id);
      } catch (e) {
        console.error('Failed to toggle service status', e);
        srv.active = !srv.active;
      }
    }
  }
};
</script>

<style scoped>
.tag-chip {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  background: rgba(0,170,255,0.12);
  color: var(--c2-accent);
  margin-right: 4px;
  margin-bottom: 2px;
}
</style>
