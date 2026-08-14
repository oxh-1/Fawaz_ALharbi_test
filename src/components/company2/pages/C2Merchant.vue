<template>
  <Company2Layout page-title="Merchants">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Merchant Partner Management</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Onboard, verify, and manage vendor stores and partner profiles.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Add New Merchant</button>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search merchants by name, email, phone..." />
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Pending">Pending Verification</option>
        </select>
        <select v-model="categoryFilter" class="c2-select">
          <option value="">All Categories</option>
          <option v-for="c in categoriesList" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>
      <span style="font-size:0.85rem;color:var(--c2-text-muted)">{{ filtered.length }} merchants</span>
    </div>

    <!-- Table -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr>
              <th>Partner Store</th>
              <th>Contact Info</th>
              <th>Category</th>
              <th>Joined Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in filtered" :key="m.id">
              <td>
                <div style="font-weight:700;color:var(--c2-text);font-size:0.95rem">{{ m.name }}</div>
                <small style="color:var(--c2-text-muted)">ID #{{ m.id }} · {{ m.city || 'Riyadh' }}</small>
              </td>
              <td>
                <div style="font-size:0.85rem">{{ m.email }}</div>
                <div style="font-size:0.75rem;color:var(--c2-text-muted)">{{ m.phone || '+966 50 000 0000' }}</div>
              </td>
              <td>
                <span class="c2-badge" style="background:rgba(0,170,255,0.1);color:var(--c2-accent)">
                  {{ m.category }}
                </span>
              </td>
              <td>
                <div style="font-size:0.82rem">{{ m.joined || (m.created_at ? m.created_at.substring(0, 10) : '2025-01-10') }}</div>
              </td>
              <td>
                <span :class="['c2-badge', 'c2-badge-' + String(m.status).toLowerCase()]">
                  {{ m.status }}
                </span>
              </td>
              <td>
                <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(m)" style="margin-right:6px">Edit</button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteMerchant(m.id)">Delete</button>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="6" style="text-align:center;padding:40px;color:var(--c2-text-muted)">
                No merchants found matching your filters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editingMerchant ? 'Edit Merchant Partner' : 'Onboard New Merchant' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Business / Merchant Name *</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. Gourmet Burger Lounge" />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Email Address *</label>
            <input v-model="form.email" type="email" class="c2-form-input" placeholder="partner@domain.sa" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Phone Number</label>
            <input v-model="form.phone" class="c2-form-input" placeholder="+966 50 123 4567" />
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Industry Category *</label>
            <select v-model="form.category" class="c2-form-select">
              <option v-for="cat in categoriesList" :key="cat" :value="cat">{{ cat }}</option>
            </select>
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Verification Status</label>
            <select v-model="form.status" class="c2-form-select">
              <option value="Active">Active</option>
              <option value="Pending">Pending Verification</option>
              <option value="Inactive">Inactive / Suspended</option>
            </select>
          </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveMerchant">{{ editingMerchant ? 'Update Merchant' : 'Save Merchant' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { merchantApi, categoryApi } from '@/services/api';

export default {
  name: 'C2Merchant',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      statusFilter: '',
      categoryFilter: '',
      showModal: false,
      editingMerchant: null,
      form: { name: '', email: '', phone: '', category: 'Retail', status: 'Active' },
      merchants: [],
      categoriesList: ['Food & Beverage', 'Retail', 'Healthcare', 'Beauty & Wellness', 'Technology', 'Sports & Fitness', 'Education', 'Automotive', 'Services']
    };
  },
  async mounted() {
    await this.fetchMerchants();
    this.fetchCategories();
  },
  computed: {
    filtered() {
      return this.merchants.filter(m => {
        const q = this.search.toLowerCase();
        const matchSearch = !q || (m.name && m.name.toLowerCase().includes(q)) ||
                                  (m.email && m.email.toLowerCase().includes(q)) ||
                                  (m.phone && m.phone.includes(q));
        const matchStatus = !this.statusFilter || String(m.status).toLowerCase() === this.statusFilter.toLowerCase();
        const matchCategory = !this.categoryFilter || m.category === this.categoryFilter;
        return matchSearch && matchStatus && matchCategory;
      });
    }
  },
  methods: {
    async fetchMerchants() {
      try {
        const res = await merchantApi.list();
        this.merchants = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load merchants', e);
      }
    },
    async fetchCategories() {
      try {
        const res = await categoryApi.list();
        const list = Array.isArray(res) ? res : (res.data || []);
        if (list.length > 0) {
          this.categoriesList = list.map(c => c.name);
        }
      } catch (e) {
        console.error(e);
      }
    },
    openModal(m = null) {
      this.editingMerchant = m;
      this.form = m ? { ...m } : { name: '', email: '', phone: '', category: this.categoriesList[0] || 'Retail', status: 'Active' };
      this.showModal = true;
    },
    async saveMerchant() {
      if (!this.form.name.trim() || !this.form.email.trim()) {
        alert('Please fill in required fields.');
        return;
      }
      try {
        if (this.editingMerchant) {
          await merchantApi.update(this.editingMerchant.id, this.form);
        } else {
          await merchantApi.create(this.form);
        }
        await this.fetchMerchants();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to save merchant', e);
      }
    },
    async deleteMerchant(id) {
      if (confirm('Are you sure you want to remove this merchant?')) {
        try {
          await merchantApi.delete(id);
          await this.fetchMerchants();
        } catch (e) {
          console.error('Failed to delete merchant', e);
        }
      }
    }
  }
};
</script>

<style scoped>
</style>
