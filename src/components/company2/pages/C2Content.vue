<template>
  <Company2Layout page-title="Content Management">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Content & Legal Pages (CMS)</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Manage Terms of Service, Privacy Policy, FAQs, About Us, and custom SEO pages.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="newPage">+ Create New Page</button>
    </div>

    <div class="c2-grid-2" style="align-items:flex-start">
      <!-- Left: Pages List -->
      <div class="c2-card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--c2-border);display:flex;justify-content:space-between;align-items:center">
          <h3 class="c2-card-title" style="margin:0;font-size:0.95rem">📄 Published Pages ({{ pages.length }})</h3>
        </div>
        <div class="page-list">
          <div
            v-for="page in pages" :key="page.id"
            :class="['page-item', { selected: selected && selected.id === page.id }]"
            @click="select(page)">
            <div class="page-item-meta">
              <span class="page-item-title">{{ page.title }}</span>
              <span :class="['c2-badge', 'c2-badge-' + String(page.status).toLowerCase()]" style="font-size:0.68rem">
                {{ page.status }}
              </span>
            </div>
            <div class="page-item-sub">
              <code>{{ page.slug }}</code> · Updated {{ page.updated || 'Recently' }}
            </div>
          </div>

          <div v-if="pages.length === 0" class="c2-empty" style="padding:40px;text-align:center">
            <div class="c2-empty-icon" style="font-size:2.5rem">📝</div>
            <div class="c2-empty-text" style="color:var(--c2-text-muted)">No CMS pages found.</div>
          </div>
        </div>
      </div>

      <!-- Right: Page Content Editor -->
      <div class="c2-card" v-if="selected">
        <div class="c2-section-header" style="margin-bottom:14px">
          <h3 class="c2-section-title" style="margin-bottom:0;font-size:1.05rem">✍️ Editing: {{ selected.title }}</h3>
          <div style="display:flex;gap:8px">
            <button
              :class="['c2-btn c2-btn-sm', String(selected.status).toLowerCase() === 'published' ? 'c2-btn-warning' : 'c2-btn-success']"
              @click="togglePublish">
              {{ String(selected.status).toLowerCase() === 'published' ? 'Unpublish (Draft)' : 'Publish Live' }}
            </button>
            <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deletePage(selected.id)">Delete</button>
          </div>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Page Title *</label>
          <input v-model="selected.title" class="c2-form-input" placeholder="e.g. Terms of Service" @input="updateSlug" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">URL Slug *</label>
          <input v-model="selected.slug" class="c2-form-input" placeholder="/terms-of-service" />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">
            Page Content (Markdown / HTML Supported)
            <span style="font-size:0.75rem;color:var(--c2-text-muted);margin-left:8px">
              ({{ (selected.content || '').length }} characters)
            </span>
          </label>
          <textarea v-model="selected.content" class="c2-form-input" style="min-height:220px;font-family:monospace;font-size:0.88rem;line-height:1.6" placeholder="Write page content here..."></textarea>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">SEO Meta Description</label>
          <textarea v-model="selected.meta" class="c2-form-input" rows="2" placeholder="Search engine snippet description (recommended 150-160 chars)"></textarea>
        </div>

        <button class="c2-btn c2-btn-primary" @click="savePage" style="width:100%">
          💾 Save Changes
        </button>
        <p v-if="saved" style="color:var(--c2-success,#27ae60);text-align:center;margin-top:8px;font-size:0.875rem">
          ✓ Changes saved successfully!
        </p>
      </div>

      <div class="c2-card" v-else>
        <div class="c2-empty" style="padding:60px;text-align:center">
          <div class="c2-empty-icon" style="font-size:3rem;margin-bottom:8px">📝</div>
          <div class="c2-empty-text" style="color:var(--c2-text-muted)">Select a page on the left to edit, or click <strong>+ Create New Page</strong>.</div>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { contentApi } from '@/services/api';

export default {
  name: 'C2Content',
  components: { Company2Layout },
  data() {
    return {
      selected: null,
      saved: false,
      pages: []
    };
  },
  async mounted() {
    await this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        const res = await contentApi.list();
        this.pages = Array.isArray(res) ? res : (res.data || []);
        if (this.selected) {
          this.selected = this.pages.find(p => p.id === this.selected.id) || null;
        } else if (this.pages.length > 0) {
          this.selected = { ...this.pages[0] };
        }
      } catch (e) {
        console.error('Failed to load content pages', e);
      }
    },
    select(p) {
      this.selected = { ...p };
      this.saved = false;
    },
    updateSlug() {
      if (this.selected && this.selected.title) {
        this.selected.slug = '/' + this.selected.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
      }
    },
    async togglePublish() {
      if (!this.selected) return;
      try {
        await contentApi.togglePublish(this.selected.id);
        await this.fetchData();
      } catch (e) {
        console.error('Failed to toggle publish', e);
      }
    },
    async savePage() {
      if (!this.selected || !this.selected.title.trim()) {
        alert('Please enter a page title.');
        return;
      }
      try {
        if (this.selected.id) {
          await contentApi.update(this.selected.id, this.selected);
        } else {
          await contentApi.create(this.selected);
        }
        await this.fetchData();
        this.saved = true;
        setTimeout(() => (this.saved = false), 2500);
      } catch (e) {
        console.error('Failed to save page', e);
      }
    },
    async deletePage(id) {
      if (confirm('Are you sure you want to permanently delete this page?')) {
        try {
          await contentApi.delete(id);
          this.selected = null;
          await this.fetchData();
        } catch (e) {
          console.error('Failed to delete page', e);
        }
      }
    },
    newPage() {
      this.selected = {
        title: 'New Page',
        slug: '/new-page',
        content: '# New Page Title\n\nWrite your content here...',
        meta: 'Meta description for SEO...',
        status: 'Draft'
      };
    }
  }
};
</script>

<style scoped>
.page-list {
  max-height: 520px;
  overflow-y: auto;
}
.page-item {
  padding: 14px 18px;
  cursor: pointer;
  border-bottom: 1px solid var(--c2-border, #e8ecf0);
  transition: background 0.2s ease;
}
.page-item:hover {
  background: rgba(0, 170, 255, 0.05);
}
.page-item.selected {
  background: rgba(0, 170, 255, 0.1);
  border-left: 3px solid var(--c2-accent);
}
.page-item-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.page-item-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--c2-text);
}
.page-item-sub {
  font-size: 0.75rem;
  color: var(--c2-text-muted);
}
</style>
