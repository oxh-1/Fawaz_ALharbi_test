<template>
  <Company2Layout page-title="Contact & Support Inbox">
    <!-- Header -->
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Support Inbox & Inquiries</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          Customer support messages, merchant partnership requests, and contact inquiries.
        </p>
      </div>
      <span v-if="unreadCount > 0" class="c2-badge c2-badge-pending" style="font-size:0.85rem;padding:6px 12px">
        📬 {{ unreadCount }} Unread Message{{ unreadCount > 1 ? 's' : '' }}
      </span>
    </div>

    <!-- Main Grid -->
    <div class="c2-grid-2">
      <!-- Left: Inbox List -->
      <div class="c2-card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--c2-border);display:flex;gap:10px">
          <input v-model="search" class="c2-search-input" placeholder="🔍 Search inquiries..." style="font-size:0.82rem;padding:6px 12px" />
          <select v-model="filterRead" class="c2-select" style="font-size:0.82rem;padding:6px 10px">
            <option value="">All</option>
            <option value="unread">Unread</option>
            <option value="read">Read</option>
          </select>
        </div>

        <div class="inbox-list">
          <div
            v-for="msg in filteredMessages" :key="msg.id"
            :class="['inbox-item', { unread: !msg.read, selected: selectedMsg && selectedMsg.id === msg.id }]"
            @click="selectMsg(msg)">
            <div class="inbox-meta">
              <span class="inbox-from">
                <span v-if="!msg.read" class="unread-dot"></span>
                {{ msg.name }}
              </span>
              <span class="inbox-time">{{ msg.date }}</span>
            </div>
            <div class="inbox-subject">{{ msg.subject }}</div>
            <div class="inbox-preview">{{ (msg.message || '').substring(0, 65) }}...</div>
          </div>

          <div v-if="filteredMessages.length === 0" class="c2-empty" style="padding:40px;text-align:center">
            <div class="c2-empty-icon" style="font-size:2.5rem">📭</div>
            <div class="c2-empty-text" style="color:var(--c2-text-muted)">No inquiries match your filter.</div>
          </div>
        </div>
      </div>

      <!-- Right: Message Detail & Reply OR New Inquiry Form -->
      <div>
        <!-- Message Detail & Reply Box -->
        <div v-if="selectedMsg" class="c2-card">
          <div class="c2-section-header" style="margin-bottom:12px">
            <h3 class="c2-section-title" style="margin-bottom:0;font-size:1.1rem">{{ selectedMsg.subject }}</h3>
            <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="selectedMsg=null">✕ Close</button>
          </div>
          
          <div style="background:var(--c2-bg);padding:12px 14px;border-radius:8px;margin-bottom:14px">
            <div style="font-size:0.85rem;color:var(--c2-text)">
              <strong>From:</strong> {{ selectedMsg.name }} &lt;{{ selectedMsg.email }}&gt;
            </div>
            <div style="font-size:0.75rem;color:var(--c2-text-muted);margin-top:2px">
              Received: {{ selectedMsg.date }} · ID #{{ selectedMsg.id }}
            </div>
          </div>

          <div style="font-size:0.92rem;line-height:1.6;color:var(--c2-text);white-space:pre-wrap;margin-bottom:20px;padding:4px 2px">
            {{ selectedMsg.message }}
          </div>

          <!-- Quick Reply Area -->
          <div style="border-top:1px solid var(--c2-border);padding-top:16px">
            <h4 style="font-size:0.9rem;margin-bottom:8px">💬 Send Email Reply to Customer</h4>
            <div class="c2-form-group">
              <textarea v-model="replyText" class="c2-form-input" rows="3" placeholder="Type your response to the customer..."></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteMsg(selectedMsg.id)">🗑 Delete Message</button>
              <button class="c2-btn c2-btn-primary c2-btn-sm" @click="sendReply" :disabled="!replyText.trim()">
                ✉️ Send Reply
              </button>
            </div>
            <p v-if="replySent" style="color:var(--c2-success,#27ae60);font-size:0.8rem;margin-top:8px">
              ✓ Reply sent successfully via email notification!
            </p>
          </div>
        </div>

        <!-- Submit Inquiry Form (when nothing selected) -->
        <div v-else class="c2-card">
          <h3 class="c2-card-title">✉️ Submit Test Inquiry / Ticket</h3>
          <p style="font-size:0.82rem;color:var(--c2-text-muted);margin-bottom:16px">
            Create a simulated customer support inquiry to test the workflow.
          </p>

          <div class="c2-form-group">
            <label class="c2-form-label">Full Name *</label>
            <input v-model="form.name" class="c2-form-input" placeholder="e.g. Faisal Al-Qahtani" />
          </div>

          <div class="c2-form-group">
            <label class="c2-form-label">Email Address *</label>
            <input v-model="form.email" type="email" class="c2-form-input" placeholder="faisal@example.com" />
          </div>

          <div class="c2-form-group">
            <label class="c2-form-label">Subject *</label>
            <input v-model="form.subject" class="c2-form-input" placeholder="e.g. Corporate Merchant Partnership" />
          </div>

          <div class="c2-form-group">
            <label class="c2-form-label">Message *</label>
            <textarea v-model="form.message" class="c2-form-input" rows="4" placeholder="Describe the inquiry or issue in detail..."></textarea>
          </div>

          <button class="c2-btn c2-btn-primary" @click="sendMessage" style="width:100%">
            Submit Inquiry
          </button>
          <p v-if="sent" style="color:var(--c2-success,#27ae60);text-align:center;margin-top:10px;font-size:0.875rem">
            ✓ Inquiry created and logged into inbox!
          </p>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { contactApi } from '@/services/api';

export default {
  name: 'C2Contact',
  components: { Company2Layout },
  data() {
    return {
      search: '',
      filterRead: '',
      selectedMsg: null,
      sent: false,
      replySent: false,
      replyText: '',
      form: { name: '', email: '', subject: '', message: '' },
      messages: []
    };
  },
  async mounted() {
    await this.fetchData();
  },
  computed: {
    unreadCount() {
      return this.messages.filter(m => !m.read).length;
    },
    filteredMessages() {
      return this.messages.filter(m => {
        const q = this.search.toLowerCase();
        const matchQ = !q || (m.name && m.name.toLowerCase().includes(q)) ||
                              (m.subject && m.subject.toLowerCase().includes(q)) ||
                              (m.message && m.message.toLowerCase().includes(q));
        const matchRead = !this.filterRead || (this.filterRead === 'unread' ? !m.read : m.read);
        return matchQ && matchRead;
      });
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await contactApi.list();
        this.messages = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load contact messages', e);
      }
    },
    async selectMsg(msg) {
      if (!msg.read) {
        msg.read = true;
        try {
          await contactApi.markRead(msg.id);
        } catch (e) {
          console.error(e);
        }
      }
      this.selectedMsg = msg;
      this.replyText = '';
      this.replySent = false;
    },
    async deleteMsg(id) {
      if (confirm('Delete this message permanently?')) {
        try {
          await contactApi.delete(id);
          await this.fetchData();
          this.selectedMsg = null;
        } catch (e) {
          console.error('Failed to delete message', e);
        }
      }
    },
    async sendMessage() {
      if (!this.form.name || !this.form.email || !this.form.subject || !this.form.message) {
        alert('Please fill in all required fields.');
        return;
      }
      try {
        await contactApi.send(this.form);
        await this.fetchData();
        this.form = { name: '', email: '', subject: '', message: '' };
        this.sent = true;
        setTimeout(() => (this.sent = false), 3500);
      } catch (e) {
        console.error('Failed to send contact message', e);
      }
    },
    async sendReply() {
      if (!this.selectedMsg || !this.replyText.trim()) return;
      try {
        await contactApi.send({
          name: 'Platform Support',
          email: 'support@company2.sa',
          subject: 'Re: ' + this.selectedMsg.subject,
          message: this.replyText
        });
        this.replySent = true;
        setTimeout(() => {
          this.replySent = false;
          this.replyText = '';
        }, 3000);
      } catch (e) {
        console.error('Failed to send reply', e);
      }
    }
  }
};
</script>

<style scoped>
.inbox-list {
  max-height: 480px;
  overflow-y: auto;
}
.inbox-item {
  padding: 14px 18px;
  cursor: pointer;
  border-bottom: 1px solid var(--c2-border, #e8ecf0);
  transition: background 0.2s ease;
}
.inbox-item:hover {
  background: rgba(0, 170, 255, 0.05);
}
.inbox-item.unread {
  border-left: 3px solid var(--c2-accent);
  background: rgba(0, 170, 255, 0.03);
}
.inbox-item.selected {
  background: rgba(0, 170, 255, 0.1);
}
.inbox-meta {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}
.inbox-from {
  font-weight: 700;
  font-size: 0.88rem;
  display: flex;
  align-items: center;
  gap: 6px;
}
.unread-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--c2-accent);
}
.inbox-time {
  font-size: 0.72rem;
  color: var(--c2-text-muted);
}
.inbox-subject {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--c2-text);
  margin-bottom: 3px;
}
.inbox-preview {
  font-size: 0.75rem;
  color: var(--c2-text-muted);
}
</style>
