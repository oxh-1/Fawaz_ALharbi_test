<template>
  <Company2Layout page-title="Contact Us">
    <div class="c2-grid-2">
      <!-- Inbox -->
      <div class="c2-card" style="padding:0">
        <div style="padding:16px 20px;border-bottom:1px solid var(--c2-border)">
          <h3 class="c2-card-title" style="margin:0">📬 Inbox ({{ messages.length }})</h3>
        </div>
        <div class="inbox-list">
          <div
            v-for="msg in messages" :key="msg.id"
            :class="['inbox-item', { unread: !msg.read, selected: selectedMsg && selectedMsg.id === msg.id }]"
            @click="selectMsg(msg)">
            <div class="inbox-meta">
              <span class="inbox-from">{{ msg.name }}</span>
              <span class="inbox-time">{{ msg.date }}</span>
            </div>
            <div class="inbox-subject">{{ msg.subject }}</div>
            <div class="inbox-preview">{{ msg.message.substring(0, 60) }}...</div>
          </div>
          <div v-if="messages.length === 0" class="c2-empty"><div class="c2-empty-icon">📭</div><div class="c2-empty-text">Empty inbox</div></div>
        </div>
      </div>

      <!-- Right: Message Detail OR Contact Form -->
      <div>
        <!-- Message Detail -->
        <div v-if="selectedMsg" class="c2-card">
          <div class="c2-section-header">
            <h3 class="c2-section-title">{{ selectedMsg.subject }}</h3>
            <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="selectedMsg=null">✕ Close</button>
          </div>
          <p style="font-size:0.82rem;color:var(--c2-text-muted)">From: <strong>{{ selectedMsg.name }}</strong> &lt;{{ selectedMsg.email }}&gt; · {{ selectedMsg.date }}</p>
          <p style="font-size:0.9rem;line-height:1.6;margin-top:14px">{{ selectedMsg.message }}</p>
          <div style="margin-top:16px;display:flex;gap:8px">
            <button class="c2-btn c2-btn-danger c2-btn-sm" @click="deleteMsg(selectedMsg.id)">🗑 Delete</button>
          </div>
        </div>

        <!-- Contact Form -->
        <div v-else class="c2-card">
          <h3 class="c2-card-title">✉️ Send a Message</h3>
          <div class="c2-form-group">
            <label class="c2-form-label">Your Name</label>
            <input v-model="form.name" class="c2-form-input" placeholder="Full name" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Email</label>
            <input v-model="form.email" type="email" class="c2-form-input" placeholder="email@example.com" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Subject</label>
            <input v-model="form.subject" class="c2-form-input" placeholder="Subject" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Message</label>
            <textarea v-model="form.message" class="c2-form-textarea" placeholder="Write your message..."></textarea>
          </div>
          <button class="c2-btn c2-btn-primary" @click="sendMessage" style="width:100%">Send Message</button>
          <p v-if="sent" style="color:var(--c2-success,#27ae60);text-align:center;margin-top:10px;font-size:0.875rem">✓ Message sent successfully!</p>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Contact',
  components: { Company2Layout },
  data() {
    return {
      selectedMsg: null, sent: false,
      form: { name: '', email: '', subject: '', message: '' },
      messages: [
        { id: 1, name: 'Ahmed Al-Harbi', email: 'ahmed@mail.com', subject: 'Issue with booking', message: 'I booked a session yesterday but received no confirmation email. Can you please check the status of booking #1023?', date: '2025-04-02', read: false },
        { id: 2, name: 'Sara Khalid', email: 'sara@example.com', subject: 'Partnership inquiry', message: 'We represent a chain of 15 restaurants and would like to discuss a bulk merchant registration. Please contact us.', date: '2025-04-01', read: true },
        { id: 3, name: 'Omar Faris', email: 'omar@corp.sa', subject: 'Billing question', message: 'I was charged twice for the same subscription. Please review transaction ID TX-8847.', date: '2025-03-30', read: false }
      ]
    };
  },
  methods: {
    selectMsg(msg) { msg.read = true; this.selectedMsg = msg; },
    deleteMsg(id) { this.messages = this.messages.filter(m => m.id !== id); this.selectedMsg = null; },
    sendMessage() {
      if (!this.form.name || !this.form.email || !this.form.message) return;
      this.messages.unshift({ id: Date.now(), ...this.form, date: new Date().toISOString().split('T')[0], read: true });
      this.form = { name: '', email: '', subject: '', message: '' };
      this.sent = true;
      setTimeout(() => (this.sent = false), 3000);
    }
  }
};
</script>

<style scoped>
.inbox-list { max-height: 420px; overflow-y: auto; }
.inbox-item { padding: 14px 20px; cursor: pointer; border-bottom: 1px solid var(--c2-border, #e8ecf0); transition: background 0.2s; }
.inbox-item:hover { background: rgba(0,170,255,0.05); }
.inbox-item.unread { border-left: 3px solid #00aaff; }
.inbox-item.selected { background: rgba(0,170,255,0.1); }
.inbox-meta { display: flex; justify-content: space-between; margin-bottom: 3px; }
.inbox-from { font-weight: 700; font-size: 0.875rem; }
.inbox-time { font-size: 0.72rem; color: var(--c2-text-muted); }
.inbox-subject { font-size: 0.82rem; font-weight: 600; margin-bottom: 3px; }
.inbox-preview { font-size: 0.75rem; color: var(--c2-text-muted); }
</style>
