<template>
  <Company2Layout page-title="Categories">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Category Management</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Organize services and merchants into browsable industry categories.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Add Category</button>
    </div>

    <!-- Toolbar -->
    <div class="c2-toolbar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input v-model="search" class="c2-search-input" placeholder="🔍 Search categories by name..." />
        <select v-model="statusFilter" class="c2-select">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <span style="font-size:0.85rem;color:var(--c2-text-muted)">{{ filtered.length }} categories</span>
    </div>

    <!-- Category Grid -->
    <div class="cat-grid">
      <div v-for="cat in filtered" :key="cat.id" class="cat-card c2-card">
        <div class="cat-icon">{{ cat.icon || '📦' }}</div>
        <div class="cat-info">
          <div class="cat-name">{{ cat.name }}</div>
          <div class="cat-services">{{ cat.service_count || cat.services || 0 }} active services</div>
          <span :class="['c2-badge', 'c2-badge-' + String(cat.status).toLowerCase()]">
            {{ cat.status }}
          </span>
        </div>
        <div class="cat-actions">
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="toggleStatus(cat)" title="Toggle Active">
            {{ String(cat.status).toLowerCase() === 'active' ? 'Disable' : 'Enable' }}
          </button>
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(cat)">Edit</button>
          <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteCategory(cat.id)">Delete</button>
        </div>
      </div>
      
      <div v-if="filtered.length === 0" class="c2-empty" style="grid-column: 1 / -1; padding: 40px; text-align: center;">
        <div class="c2-empty-icon" style="font-size: 3rem; margin-bottom: 8px;">📂</div>
        <div class="c2-empty-text" style="color: var(--c2-text-muted);">No categories match your search.</div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editing ? 'Edit Category' : 'Create New Category' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>
        
        <div class="c2-form-group">
          <label class="c2-form-label">Category Name *</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. Health & Fitness" />
        </div>
        
        <div class="c2-form-group">
          <label class="c2-form-label">Icon / Emoji (Pick or Type)</label>
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
            <input v-model="form.icon" class="c2-form-input" style="width:70px;text-align:center;font-size:1.4rem" maxlength="4" />
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <span v-for="emoji in popularEmojis" :key="emoji" class="emoji-picker-btn" @click="form.icon = emoji">
                {{ emoji }}
              </span>
            </div>
          </div>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Status</label>
          <select v-model="form.status" class="c2-form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="saveCategory">{{ editing ? 'Update Category' : 'Save Category' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { categoryApi } from '@/services/api';

export default {
  name: 'C2Categories',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      statusFilter: '',
      showModal: false,
      editing: null,
      form: { name: '', icon: '📦', status: 'active' },
      categories: [],
      popularEmojis: ['🍔', '🛍️', '💻', '🏥', '💆', '🏋️', '📚', '🚗', '🎨', '✈️']
    };
  },
  async mounted() {
    await this.fetchData();
  },
  computed: {
    filtered() {
      return this.categories.filter(c => {
        const matchSearch = !this.search || c.name.toLowerCase().includes(this.search.toLowerCase());
        const matchStatus = !this.statusFilter || String(c.status).toLowerCase() === this.statusFilter.toLowerCase();
        return matchSearch && matchStatus;
      });
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await categoryApi.list();
        this.categories = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load categories', e);
      }
    },
    openModal(c = null) {
      this.editing = c;
      this.form = c ? { ...c, status: String(c.status).toLowerCase() } : { name: '', icon: '📦', status: 'active' };
      this.showModal = true;
    },
    async saveCategory() {
      if (!this.form.name.trim()) {
        alert('Please enter a category name.');
        return;
      }
      try {
        if (this.editing) {
          await categoryApi.update(this.editing.id, this.form);
        } else {
          await categoryApi.create(this.form);
        }
        await this.fetchData();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to save category', e);
      }
    },
    async toggleStatus(cat) {
      try {
        await categoryApi.update(cat.id, {
          status: String(cat.status).toLowerCase() === 'active' ? 'inactive' : 'active'
        });
        await this.fetchData();
      } catch (e) {
        console.error('Failed to toggle status', e);
      }
    },
    async deleteCategory(id) {
      if (confirm('Are you sure you want to delete this category?')) {
        try {
          await categoryApi.delete(id);
          await this.fetchData();
        } catch (e) {
          console.error('Failed to delete category', e);
        }
      }
    }
  }
};
</script>

<style scoped>
.cat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 16px;
}
.cat-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  padding: 22px 18px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.cat-icon {
  font-size: 2.6rem;
  line-height: 1;
}
.cat-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--c2-text);
}
.cat-services {
  font-size: 0.8rem;
  color: var(--c2-text-muted);
  margin: 2px 0 6px 0;
}
.cat-actions {
  display: flex;
  gap: 6px;
  margin-top: 8px;
  flex-wrap: wrap;
  justify-content: center;
}
.emoji-picker-btn {
  font-size: 1.2rem;
  padding: 4px 8px;
  border-radius: 6px;
  cursor: pointer;
  background: var(--c2-bg);
  transition: background 0.15s;
}
.emoji-picker-btn:hover {
  background: rgba(0,170,255,0.15);
}
</style>
