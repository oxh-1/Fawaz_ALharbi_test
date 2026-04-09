<template>
  <Company2Layout page-title="Services Link">
    <div class="c2-section-header">
      <h2 class="c2-section-title">Service Links</h2>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Link Service</button>
    </div>

    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search services..." />
        <select v-model="categoryFilter" class="c2-select">
          <option value="">All Categories</option>
          <option v-for="c in uniqueCategories" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>
    </div>

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
              <td><strong>{{ srv.name }}</strong></td>
              <td>{{ srv.category }}</td>
              <td>{{ srv.merchant }}</td>
              <td>
                <span v-for="tag in srv.tags" :key="tag" class="tag-chip">{{ tag }}</span>
              </td>
              <td>
                <label class="c2-switch">
                  <input type="checkbox" :checked="srv.active" @change="toggleService(srv)" />
                  <span class="c2-switch-slider"></span>
                </label>
              </td>
              <td>
                <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(srv)" style="margin-right:6px">Edit</button>
                <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteService(srv.id)">Remove</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editing ? 'Edit Service Link' : 'Link New Service' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        <div class="c2-form-group">
          <label class="c2-form-label">Service Name</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. Hair Cut & Style" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Category</label>
          <select v-model="form.category" class="c2-form-select">
            <option>Food & Beverage</option><option>Retail</option><option>Healthcare</option>
            <option>Beauty & Wellness</option><option>Sports & Fitness</option>
          </select>
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Merchant</label>
          <input v-model="form.merchant" class="c2-form-input" placeholder="Merchant name" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Tags (comma-separated)</label>
          <input v-model="tagsInput" class="c2-form-input" placeholder="e.g. premium, featured, new" />
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveService">{{ editing ? 'Update' : 'Link' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Services',
  components: { Company2Layout },
  data() {
    return {
      search: '', categoryFilter: '', showModal: false, editing: null, tagsInput: '',
      form: { name: '', category: 'Retail', merchant: '', tags: [], active: true },
      services: [
        { id: 1, name: 'Gourmet Burger', category: 'Food & Beverage', merchant: 'Beta Eats', tags: ['featured', 'new'], active: true },
        { id: 2, name: 'Laptop Repair', category: 'Retail', merchant: 'Gamma Tech', tags: ['premium'], active: true },
        { id: 3, name: 'Full Body Checkup', category: 'Healthcare', merchant: 'Delta Health', tags: ['essential'], active: false },
        { id: 4, name: 'Hair & Styling', category: 'Beauty & Wellness', merchant: 'Zeta Fashion', tags: ['trending', 'featured'], active: true },
        { id: 5, name: 'Personal Training', category: 'Sports & Fitness', merchant: 'Epsilon Services', tags: ['premium'], active: true }
      ]
    };
  },
  computed: {
    uniqueCategories() { return [...new Set(this.services.map(s => s.category))]; },
    filtered() {
      return this.services.filter(s => {
        const q = this.search.toLowerCase();
        const m = !q || s.name.toLowerCase().includes(q) || s.merchant.toLowerCase().includes(q);
        const c = !this.categoryFilter || s.category === this.categoryFilter;
        return m && c;
      });
    }
  },
  methods: {
    openModal(s = null) {
      this.editing = s;
      this.form = s ? { ...s } : { name: '', category: 'Retail', merchant: '', tags: [], active: true };
      this.tagsInput = this.form.tags.join(', ');
      this.showModal = true;
    },
    saveService() {
      if (!this.form.name.trim()) return;
      this.form.tags = this.tagsInput.split(',').map(t => t.trim()).filter(Boolean);
      if (this.editing) {
        const idx = this.services.findIndex(s => s.id === this.editing.id);
        if (idx !== -1) this.$set(this.services, idx, { ...this.form, id: this.editing.id });
      } else {
        this.services.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteService(id) { if (confirm('Remove service link?')) this.services = this.services.filter(s => s.id !== id); },
    toggleService(srv) { srv.active = !srv.active; }
  }
};
</script>

<style scoped>
.tag-chip {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 600;
  background: rgba(0,170,255,0.12);
  color: #00aaff;
  margin-right: 4px;
  margin-bottom: 2px;
}
</style>
