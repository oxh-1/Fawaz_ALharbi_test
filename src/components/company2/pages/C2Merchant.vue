<template>
  <Company2Layout page-title="Merchants">
    <div class="c2-section-header">
      <h2 class="c2-section-title">Merchant Management</h2>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Add Merchant</button>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search merchants..." />
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Pending">Pending</option>
        </select>
      </div>
      <span style="font-size:0.82rem;color:var(--c2-text-muted)">{{ filtered.length }} results</span>
    </div>

    <!-- Table -->
    <div class="c2-card" style="padding:0">
      <div class="c2-table-wrapper">
        <table class="c2-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Category</th>
              <th>Joined</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in filtered" :key="m.id">
              <td>{{ m.id }}</td>
              <td><strong>{{ m.name }}</strong></td>
              <td>{{ m.email }}</td>
              <td>{{ m.category }}</td>
              <td>{{ m.joined }}</td>
              <td><span :class="['c2-badge', 'c2-badge-' + m.status.toLowerCase()]">{{ m.status }}</span></td>
              <td>
                <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(m)" style="margin-right:6px">Edit</button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteMerchant(m.id)">Delete</button>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="7" style="text-align:center;padding:40px;color:var(--c2-text-muted)">No merchants found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editingMerchant ? 'Edit Merchant' : 'Add Merchant' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        <div class="c2-form-group">
          <label class="c2-form-label">Name</label>
          <input v-model="form.name" class="c2-form-input" placeholder="Merchant name" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Email</label>
          <input v-model="form.email" type="email" class="c2-form-input" placeholder="email@example.com" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Category</label>
          <select v-model="form.category" class="c2-form-select">
            <option>Food & Beverage</option>
            <option>Retail</option>
            <option>Services</option>
            <option>Technology</option>
            <option>Healthcare</option>
          </select>
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Status</label>
          <select v-model="form.status" class="c2-form-select">
            <option>Active</option>
            <option>Inactive</option>
            <option>Pending</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveMerchant">{{ editingMerchant ? 'Update' : 'Create' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Merchant',
  components: { Company2Layout },
  data() {
    return {
      search: '', statusFilter: '', showModal: false, editingMerchant: null,
      form: { name: '', email: '', category: 'Retail', status: 'Active' },
      merchants: [
        { id: 1, name: 'Acme Store', email: 'acme@store.com', category: 'Retail', joined: '2025-01-10', status: 'Active' },
        { id: 2, name: 'Beta Eats', email: 'beta@eats.com', category: 'Food & Beverage', joined: '2025-02-14', status: 'Active' },
        { id: 3, name: 'Gamma Tech', email: 'g@tech.com', category: 'Technology', joined: '2025-01-28', status: 'Pending' },
        { id: 4, name: 'Delta Health', email: 'dh@health.sa', category: 'Healthcare', joined: '2024-11-05', status: 'Inactive' },
        { id: 5, name: 'Epsilon Services', email: 'eps@srv.com', category: 'Services', joined: '2025-03-01', status: 'Active' },
        { id: 6, name: 'Zeta Fashion', email: 'zeta@fashion.sa', category: 'Retail', joined: '2025-03-18', status: 'Active' }
      ]
    };
  },
  computed: {
    filtered() {
      return this.merchants.filter(m => {
        const matchSearch = !this.search || m.name.toLowerCase().includes(this.search.toLowerCase()) || m.email.toLowerCase().includes(this.search.toLowerCase());
        const matchStatus = !this.statusFilter || m.status === this.statusFilter;
        return matchSearch && matchStatus;
      });
    }
  },
  methods: {
    openModal(m = null) {
      this.editingMerchant = m;
      this.form = m ? { ...m } : { name: '', email: '', category: 'Retail', status: 'Active' };
      this.showModal = true;
    },
    saveMerchant() {
      if (!this.form.name.trim()) return;
      if (this.editingMerchant) {
        const idx = this.merchants.findIndex(m => m.id === this.editingMerchant.id);
        if (idx !== -1) this.$set(this.merchants, idx, { ...this.form, id: this.editingMerchant.id, joined: this.editingMerchant.joined });
      } else {
        const newId = Math.max(...this.merchants.map(m => m.id)) + 1;
        this.merchants.push({ ...this.form, id: newId, joined: new Date().toISOString().split('T')[0] });
      }
      this.showModal = false;
    },
    deleteMerchant(id) {
      if (confirm('Delete this merchant?')) this.merchants = this.merchants.filter(m => m.id !== id);
    }
  }
};
</script>
