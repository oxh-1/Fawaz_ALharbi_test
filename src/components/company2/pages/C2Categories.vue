<template>
  <Company2Layout page-title="Categories">
    <div class="c2-section-header">
      <h2 class="c2-section-title">Category Management</h2>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Add Category</button>
    </div>

    <div class="c2-toolbar">
      <input v-model="search" class="c2-search-input" placeholder="🔍 Search categories..." />
    </div>

    <!-- Category Grid -->
    <div class="cat-grid">
      <div v-for="cat in filtered" :key="cat.id" class="cat-card c2-card">
        <div class="cat-icon">{{ cat.icon }}</div>
        <div class="cat-info">
          <div class="cat-name">{{ cat.name }}</div>
          <div class="cat-services">{{ cat.services }} services</div>
          <span :class="['c2-badge', 'c2-badge-' + cat.status.toLowerCase()]">{{ cat.status }}</span>
        </div>
        <div class="cat-actions">
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(cat)">Edit</button>
          <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteCat(cat.id)">Delete</button>
        </div>
      </div>
      <div v-if="filtered.length === 0" class="c2-empty">
        <div class="c2-empty-icon">📂</div>
        <div class="c2-empty-text">No categories found</div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editing ? 'Edit Category' : 'Add Category' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        <div class="c2-form-group">
          <label class="c2-form-label">Category Name</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. Food & Beverage" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Icon (Emoji)</label>
          <input v-model="form.icon" class="c2-form-input" placeholder="e.g. 🍔" maxlength="4" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Status</label>
          <select v-model="form.status" class="c2-form-select">
            <option>Active</option>
            <option>Inactive</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveCat">{{ editing ? 'Update' : 'Create' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Categories',
  components: { Company2Layout },
  data() {
    return {
      search: '', showModal: false, editing: null,
      form: { name: '', icon: '📦', status: 'Active' },
      categories: [
        { id: 1, name: 'Food & Beverage', icon: '🍔', services: 14, status: 'Active' },
        { id: 2, name: 'Retail', icon: '🛍️', services: 8, status: 'Active' },
        { id: 3, name: 'Technology', icon: '💻', services: 6, status: 'Active' },
        { id: 4, name: 'Healthcare', icon: '🏥', services: 5, status: 'Active' },
        { id: 5, name: 'Beauty & Wellness', icon: '💆', services: 9, status: 'Active' },
        { id: 6, name: 'Sports & Fitness', icon: '🏋️', services: 4, status: 'Inactive' },
        { id: 7, name: 'Education', icon: '📚', services: 7, status: 'Active' },
        { id: 8, name: 'Automotive', icon: '🚗', services: 3, status: 'Active' }
      ]
    };
  },
  computed: {
    filtered() {
      if (!this.search) return this.categories;
      return this.categories.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
    }
  },
  methods: {
    openModal(c = null) {
      this.editing = c;
      this.form = c ? { ...c } : { name: '', icon: '📦', status: 'Active' };
      this.showModal = true;
    },
    saveCat() {
      if (!this.form.name.trim()) return;
      if (this.editing) {
        const idx = this.categories.findIndex(c => c.id === this.editing.id);
        if (idx !== -1) this.$set(this.categories, idx, { ...this.form, id: this.editing.id, services: this.editing.services });
      } else {
        this.categories.push({ ...this.form, id: Date.now(), services: 0 });
      }
      this.showModal = false;
    },
    deleteCat(id) {
      if (confirm('Delete this category?')) this.categories = this.categories.filter(c => c.id !== id);
    }
  }
};
</script>

<style scoped>
.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.cat-card { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; padding: 20px 16px; }
.cat-icon { font-size: 2.4rem; }
.cat-name { font-size: 0.95rem; font-weight: 700; }
.cat-services { font-size: 0.78rem; color: var(--c2-text-muted); margin: 2px 0; }
.cat-actions { display: flex; gap: 6px; margin-top: 6px; }
</style>
