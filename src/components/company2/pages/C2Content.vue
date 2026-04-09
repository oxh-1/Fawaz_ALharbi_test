<template>
  <Company2Layout page-title="Content">
    <div class="c2-grid-2" style="align-items:flex-start">
      <!-- Page List -->
      <div class="c2-card" style="padding:0">
        <div style="padding:16px 20px;border-bottom:1px solid var(--c2-border);display:flex;justify-content:space-between;align-items:center">
          <h3 class="c2-card-title" style="margin:0">📄 Pages</h3>
          <button class="c2-btn c2-btn-primary c2-btn-sm" @click="newPage">+ New Page</button>
        </div>
        <div class="page-list">
          <div
            v-for="page in pages" :key="page.id"
            :class="['page-item', { selected: selected && selected.id === page.id }]"
            @click="select(page)">
            <div class="page-item-meta">
              <span class="page-item-title">{{ page.title }}</span>
              <span :class="['c2-badge', 'c2-badge-' + page.status.toLowerCase()]" style="font-size:0.68rem">{{ page.status }}</span>
            </div>
            <div class="page-item-sub">Updated {{ page.updated }}</div>
          </div>
        </div>
      </div>

      <!-- Editor -->
      <div class="c2-card" v-if="selected">
        <div class="c2-section-header">
          <h3 class="c2-section-title">✍️ Edit: {{ selected.title }}</h3>
          <div style="display:flex;gap:8px">
            <button :class="['c2-btn c2-btn-sm', selected.status==='Published'?'c2-btn-warning':'c2-btn-success']" @click="togglePublish">
              {{ selected.status === 'Published' ? 'Unpublish' : 'Publish' }}
            </button>
            <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deletePage(selected.id)">Delete</button>
          </div>
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Page Title</label>
          <input v-model="selected.title" class="c2-form-input" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Slug</label>
          <input v-model="selected.slug" class="c2-form-input" placeholder="/about-us" />
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Content</label>
          <textarea v-model="selected.content" class="c2-form-textarea" style="min-height:200px" placeholder="Write page content..."></textarea>
        </div>
        <div class="c2-form-group">
          <label class="c2-form-label">Meta Description (SEO)</label>
          <textarea v-model="selected.meta" class="c2-form-textarea" style="min-height:60px" placeholder="Short page description for search engines"></textarea>
        </div>
        <button class="c2-btn c2-btn-primary" @click="savePage" style="width:100%">💾 Save Changes</button>
        <p v-if="saved" style="color:var(--c2-success,#27ae60);text-align:center;margin-top:8px;font-size:0.875rem">✓ Saved!</p>
      </div>
      <div class="c2-card" v-else>
        <div class="c2-empty"><div class="c2-empty-icon">📝</div><div class="c2-empty-text">Select a page to edit or create a new one</div></div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Content',
  components: { Company2Layout },
  data() {
    return {
      selected: null, saved: false,
      pages: [
        { id: 1, title: 'About Us', slug: '/about-us', content: 'We are Company 2, a leading platform...', meta: 'Learn more about Company 2 and our mission.', status: 'Published', updated: '2025-04-01' },
        { id: 2, title: 'Terms of Service', slug: '/terms', content: '1. Agreement to Terms...', meta: 'Company 2 terms of service.', status: 'Published', updated: '2025-03-20' },
        { id: 3, title: 'Privacy Policy', slug: '/privacy', content: 'Your privacy is important to us...', meta: 'Company 2 privacy policy.', status: 'Published', updated: '2025-03-20' },
        { id: 4, title: 'FAQ', slug: '/faq', content: 'Frequently Asked Questions...', meta: 'Get answers to common questions.', status: 'Draft', updated: '2025-04-03' },
        { id: 5, title: 'Careers', slug: '/careers', content: 'Join our team...', meta: 'Career opportunities at Company 2.', status: 'Draft', updated: '2025-04-02' }
      ]
    };
  },
  methods: {
    select(p) { this.selected = { ...p }; this.saved = false; },
    togglePublish() { this.selected.status = this.selected.status === 'Published' ? 'Draft' : 'Published'; },
    savePage() {
      const idx = this.pages.findIndex(p => p.id === this.selected.id);
      if (idx !== -1) {
        this.$set(this.pages, idx, { ...this.selected, updated: new Date().toISOString().split('T')[0] });
        this.selected = { ...this.pages[idx] };
      }
      this.saved = true;
      setTimeout(() => (this.saved = false), 2000);
    },
    deletePage(id) {
      if (confirm('Delete this page?')) { this.pages = this.pages.filter(p => p.id !== id); this.selected = null; }
    },
    newPage() {
      const p = { id: Date.now(), title: 'New Page', slug: '/new-page', content: '', meta: '', status: 'Draft', updated: new Date().toISOString().split('T')[0] };
      this.pages.push(p);
      this.select(p);
    }
  }
};
</script>

<style scoped>
.page-list { max-height: 480px; overflow-y: auto; }
.page-item { padding: 12px 20px; cursor: pointer; border-bottom: 1px solid var(--c2-border, #e8ecf0); transition: background 0.2s; }
.page-item:hover { background: rgba(0,170,255,0.05); }
.page-item.selected { background: rgba(0,170,255,0.1); border-left: 3px solid #00aaff; }
.page-item-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
.page-item-title { font-size: 0.875rem; font-weight: 600; }
.page-item-sub { font-size: 0.72rem; color: var(--c2-text-muted); }
</style>
