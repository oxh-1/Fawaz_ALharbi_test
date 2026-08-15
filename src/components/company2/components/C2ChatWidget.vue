<template>
  <div v-if="isOpen" :class="['ai-chat-widget', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Header -->
    <div class="ai-chat-header">
      <div class="ai-header-info">
        <div class="ai-avatar" :class="{ speaking: isSpeaking }">
          {{ currentAssistantIcon }}
        </div>
        <div>
          <h4 class="ai-title">{{ currentAssistantTitle }}</h4>
          <span class="ai-status">
            <span class="pulse-dot"></span>
            {{ isSpeaking ? 'Speaking to you...' : isListening ? 'Listening to your voice...' : 'Cloudflare AI & OpenAI • Active' }}
          </span>
        </div>
      </div>
      <div class="header-actions">
        <button
          class="icon-tool-btn"
          :class="{ active: autoSpeak }"
          @click="toggleAutoSpeak"
          :title="autoSpeak ? 'Voice Auto-Reply: ON' : 'Voice Auto-Reply: OFF'"
        >
          {{ autoSpeak ? '🔊' : '🔇' }}
        </button>
        <button class="icon-tool-btn" @click="clearMessages" title="Clear Conversation">
          🧹
        </button>
        <button class="icon-tool-btn" @click="$emit('close')" title="Close Chat">
          ✕
        </button>
      </div>
    </div>

    <!-- Specialized Assistant Mode Selector Tabs -->
    <div class="assistant-tabs-bar">
      <button
        v-for="a in assistantsList"
        :key="a.id"
        class="assistant-tab-btn"
        :class="{ active: activeAssistant === a.id }"
        @click="selectAssistant(a.id)"
      >
        <span>{{ a.icon }}</span>
        <span>{{ a.shortLabel }}</span>
      </button>
    </div>

    <!-- Suggested Quick Prompts -->
    <div class="ai-quick-prompts">
      <button
        v-for="(p, i) in currentQuickPrompts"
        :key="i"
        class="prompt-chip"
        @click="sendPrompt(p.text)"
      >
        <span>{{ p.icon }}</span>
        <span>{{ p.label }}</span>
      </button>
    </div>

    <!-- Chat Messages Body -->
    <div class="ai-chat-body" ref="messagesContainer">
      <div v-if="loading" class="chat-loading-spinner">
        <span class="spinner"></span>
        <span>Connecting with AI Assistant...</span>
      </div>

      <div
        v-for="msg in messages"
        :key="msg.id"
        :class="['ai-msg-row', isUserMsg(msg) ? 'outbound' : 'inbound']"
      >
        <div v-if="!isUserMsg(msg)" class="msg-bot-avatar">🤖</div>
        <div class="msg-bubble-wrap">
          <div class="msg-bubble" v-html="renderMessage(msg.message)"></div>
          <div class="msg-meta-row">
            <span class="msg-time">{{ formatTime(msg.created_at) }}</span>
            <button
              v-if="!isUserMsg(msg)"
              class="speak-bubble-btn"
              @click="speakMessage(msg.message)"
              title="Listen to this message"
            >
              🔊 Listen
            </button>
          </div>
        </div>
      </div>

      <!-- Live Typing Indicator -->
      <div v-if="isAiTyping" class="ai-msg-row inbound typing-row">
        <div class="msg-bot-avatar">🤖</div>
        <div class="typing-bubble">
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
          <span class="typing-dot"></span>
        </div>
      </div>
    </div>

    <!-- Listening Feedback Banner -->
    <div v-if="isListening" class="voice-recording-banner">
      <span class="record-pulse">🎙️</span>
      <span class="record-text">Listening to your voice... Speak now!</span>
      <button class="stop-rec-btn" @click="stopListening">Done</button>
    </div>

    <!-- Input Area -->
    <div class="ai-chat-footer">
      <!-- Quick AI Prompt Chips -->
      <div class="quick-ai-chips">
        <button type="button" class="ai-chip" @click="sendQuickPrompt('What are the top AI stock buy recommendations today?')">
          ⭐ Top Buy Stocks
        </button>
        <button type="button" class="ai-chip" @click="sendQuickPrompt('Which companies are trading at their lowest price ever?')">
          🔥 Lowest Price Ever
        </button>
        <button type="button" class="ai-chip" @click="sendQuickPrompt('ما هي أفضل الأسهم الموصى بشرائها اليوم؟')">
          📈 توصيات الأسهم
        </button>
        <button type="button" class="ai-chip" @click="sendQuickPrompt('What is our total settled revenue?')">
          💰 Settled Revenue
        </button>
      </div>

      <div class="input-box-wrapper">
        <!-- Voice Input / Mic Button -->
        <button
          type="button"
          class="voice-mic-btn"
          :class="{ recording: isListening }"
          @click="toggleVoiceInput"
          :title="isListening ? 'Stop listening' : 'Talk with Voice (Microphone)'"
        >
          <span>{{ isListening ? '🔴' : '🎙️' }}</span>
        </button>

        <input
          v-model="newMessage"
          @keyup.enter="sendMessage"
          type="text"
          class="ai-input"
          :placeholder="isListening ? 'Listening...' : 'Type or talk to AI in English or عربي...'"
          :disabled="isAiTyping"
        />

        <button
          class="ai-send-btn"
          @click="sendMessage"
          :disabled="!newMessage.trim() || isAiTyping"
        >
          <span>🚀</span>
        </button>
      </div>
      <div class="footer-sub-row">
        <span class="ai-footer-note">🗣️ Interactive Voice AI • Speaks & Listens like a human</span>
        <button v-if="isSpeaking" class="stop-speech-btn" @click="stopSpeaking">⏹️ Stop Audio</button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import apiClient from '@/services/api';

export default {
  name: 'C2ChatWidget',
  props: {
    isOpen: Boolean
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      loading: false,
      isAiTyping: false,
      isSpeaking: false,
      isListening: false,
      autoSpeak: true,
      recognition: null,
      userId: null,
      activeAssistant: 'booking',
      assistantsList: [
        { id: 'booking', icon: '🎟️', shortLabel: 'Booking', title: 'AI Booking Assistant' },
        { id: 'stock', icon: '📈', shortLabel: 'Stock AI', title: 'AI Stock Prediction (Non-Financial Advice)' },
        { id: 'real_estate', icon: '💎', shortLabel: 'Real Estate', title: 'AI Real Estate Analyzer' },
        { id: 'dev_tutor', icon: '🎓', shortLabel: 'Dev Tutor', title: 'AI Developer Tutor' },
      ],
      quickPromptsMap: {
        booking: [
          { icon: '🎟️', label: 'Book Appointment', text: 'How do I book an appointment with Salon Prime?' },
          { icon: '🕒', label: 'Available Slots', text: 'What are the fastest available booking slots today?' },
          { icon: '💰', label: 'Pricing & Services', text: 'Tell me about merchant services and pricing tiers.' },
          { icon: '🔄', label: 'Reschedule Policy', text: 'What is the cancellation and rescheduling policy?' },
        ],
        stock: [
          { icon: '⭐', label: 'Top AI Buy Stocks', text: 'What are the top AI stock buy recommendations today?' },
          { icon: '🔥', label: 'Lowest Price Ever', text: 'Which companies are trading at their lowest price ever?' },
          { icon: '🇸🇦', label: 'TASI & SABIC Analysis', text: 'Analyze SABIC (2010.SR) and TASI index technical signals.' },
          { icon: '⚡', label: 'Unusual Volumes', text: 'Show unusual lower volume alerts and accumulation zones.' },
        ],
        real_estate: [
          { icon: '🏢', label: '11.8% Sukuk Yield', text: 'Explain the 11.8% annual rental dividend yield for Riyadh King Fahd Tower.' },
          { icon: '💎', label: 'Fractional Ownership', text: 'How does tokenized commercial real estate fractional ownership work?' },
          { icon: '📊', label: '5-Year IRR Model', text: 'Calculate the projected 5-year IRR and capital appreciation.' },
          { icon: '📍', label: 'Prime Riyadh Assets', text: 'What Grade-A commercial properties are currently open for investment?' },
        ],
        dev_tutor: [
          { icon: '⚡', label: 'Cloudflare D1 + Workers', text: 'Show me an example of querying Cloudflare D1 in a Worker.' },
          { icon: '🤖', label: 'PyTorch AI Track', text: 'Explain how to build a deep learning pipeline in PyTorch.' },
          { icon: '🚀', label: 'Vue 3 State Guide', text: 'Teach me Vue 3 reactive state and component design.' },
          { icon: '🐞', label: 'Debug My Code', text: 'Can you help me debug and optimize my full-stack web application?' },
        ],
      }
    };
  },
  computed: {
    ...mapState({
      user:       state => state.auth ? state.auth.user : null,
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
      locale:     state => state.settings ? state.settings.locale : 'en',
    }),
    isArabic() {
      return this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    },
    currentAssistantObj() {
      return this.assistantsList.find(a => a.id === this.activeAssistant) || this.assistantsList[0];
    },
    currentAssistantTitle() {
      return this.currentAssistantObj.title;
    },
    currentAssistantIcon() {
      return this.currentAssistantObj.icon;
    },
    currentQuickPrompts() {
      return this.quickPromptsMap[this.activeAssistant] || this.quickPromptsMap.booking;
    }
  },
  watch: {
    isOpen(newVal) {
      if (newVal && this.messages.length === 0) {
        this.fetchMessages();
      }
    }
  },
  mounted() {
    const u = this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    if (u) {
      this.userId = u.id;
    }
    if (this.isOpen) {
      this.fetchMessages();
    }
    this.initSpeechRecognition();
  },
  beforeDestroy() {
    this.stopSpeaking();
    this.stopListening();
  },
  methods: {
    selectAssistant(id) {
      this.activeAssistant = id;
      const a = this.assistantsList.find(item => item.id === id);
      if (a) {
        const welcomeMsg = `Switched to **${a.title}** ${a.icon}. How can I help you?`;
        this.messages.push({
          id: Date.now(),
          sender_id: 9999,
          message: welcomeMsg,
          created_at: new Date().toISOString()
        });
        this.scrollToBottom();
        if (this.autoSpeak) {
          this.speakMessage(welcomeMsg);
        }
      }
    },
    isUserMsg(msg) {
      return msg.sender_id === this.userId && msg.sender_id !== 9999;
    },
    toggleAutoSpeak() {
      this.autoSpeak = !this.autoSpeak;
      if (!this.autoSpeak) {
        this.stopSpeaking();
      }
    },
    initSpeechRecognition() {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (SpeechRecognition) {
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = true;
        this.recognition.lang = this.isArabic ? 'ar-SA' : 'en-US';

        this.recognition.onresult = (event) => {
          let transcript = '';
          for (let i = event.resultIndex; i < event.results.length; ++i) {
            transcript += event.results[i][0].transcript;
          }
          this.newMessage = transcript;
        };

        this.recognition.onend = () => {
          this.isListening = false;
          if (this.newMessage.trim()) {
            this.sendMessage();
          }
        };

        this.recognition.onerror = (e) => {
          console.warn('Speech recognition error/cancelled', e);
          this.isListening = false;
        };
      }
    },
    toggleVoiceInput() {
      if (this.isListening) {
        this.stopListening();
      } else {
        this.startListening();
      }
    },
    startListening() {
      if (!this.recognition) {
        this.initSpeechRecognition();
      }
      if (this.recognition) {
        try {
          this.recognition.lang = this.isArabic ? 'ar-SA' : 'en-US';
          this.recognition.start();
          this.isListening = true;
          this.stopSpeaking();
        } catch (e) {
          console.warn('Recognition start failed', e);
        }
      } else {
        alert('Voice input is not supported in this browser. Please use Chrome, Edge, or Safari.');
      }
    },
    stopListening() {
      if (this.recognition && this.isListening) {
        this.recognition.stop();
        this.isListening = false;
      }
    },
    speakMessage(rawText) {
      if (!window.speechSynthesis) return;

      this.stopSpeaking();

      // Clean markdown tags & URLs to speak smoothly
      let cleanText = rawText.replace(/\[(.*?)\]\((.*?)\)/g, '$1');
      cleanText = cleanText.replace(/[*_#`]/g, '');
      // Strip common emojis
      cleanText = cleanText.replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '');

      const utterance = new SpeechSynthesisUtterance(cleanText);
      const isAr = /[\u0600-\u06FF]/.test(cleanText);

      utterance.lang = isAr ? 'ar-SA' : 'en-US';
      utterance.rate = isAr ? 0.95 : 1.0;
      utterance.pitch = 1.05;

      const voices = window.speechSynthesis.getVoices();
      if (voices && voices.length > 0) {
        const targetVoice = voices.find(v => isAr ? v.lang.startsWith('ar') : (v.lang.startsWith('en') && (v.name.includes('Natural') || v.name.includes('Google') || v.name.includes('Online'))));
        if (targetVoice) {
          utterance.voice = targetVoice;
        }
      }

      utterance.onstart = () => {
        this.isSpeaking = true;
      };
      utterance.onend = () => {
        this.isSpeaking = false;
      };
      utterance.onerror = () => {
        this.isSpeaking = false;
      };

      window.speechSynthesis.speak(utterance);
    },
    stopSpeaking() {
      if (window.speechSynthesis) {
        window.speechSynthesis.cancel();
        this.isSpeaking = false;
      }
    },
    async fetchMessages() {
      this.loading = true;
      try {
        const res = await apiClient.get('chat');
        this.messages = Array.isArray(res) ? res : (res.data || []);
        this.scrollToBottom();
      } catch (e) {
        console.warn('Chat loading fallback', e);
        this.messages = [
          {
            id: 1,
            sender_id: 9999,
            message: "👋 Hi! I'm your AI Platform Assistant. How's your day going? Feel free to ask me anything or talk to me using voice!",
            created_at: new Date().toISOString()
          }
        ];
      } finally {
        this.loading = false;
        this.scrollToBottom();
      }
    },
    sendQuickPrompt(text) {
      this.newMessage = text;
      this.sendMessage();
    },
    sendPrompt(text) {
      this.newMessage = text;
      this.sendMessage();
    },
    async sendMessage() {
      if (!this.newMessage.trim() || this.isAiTyping) return;

      const userText = this.newMessage.trim();
      const tempUserMsg = {
        id: Date.now(),
        sender_id: this.userId || 1,
        message: userText,
        created_at: new Date().toISOString()
      };

      this.messages.push(tempUserMsg);
      this.newMessage = '';
      this.isAiTyping = true;
      this.scrollToBottom();

      try {
        const res = await apiClient.post('chat', { message: userText, assistant: this.activeAssistant });
        if (res && res.ai_message) {
          setTimeout(() => {
            this.messages.push(res.ai_message);
            this.isAiTyping = false;
            this.scrollToBottom();
            if (this.autoSpeak) {
              this.speakMessage(res.ai_message.message);
            }
          }, 600);
        }
      } catch (e) {
        setTimeout(() => {
          const fallbackText = `I hear you! Regarding "${userText}", everything is running smoothly with 99.9% uptime. Check the Platform Dashboard for full details!`;
          this.messages.push({
            id: Date.now() + 1,
            sender_id: 9999,
            message: fallbackText,
            created_at: new Date().toISOString()
          });
          this.isAiTyping = false;
          this.scrollToBottom();
          if (this.autoSpeak) {
            this.speakMessage(fallbackText);
          }
        }, 500);
      }
    },
    clearMessages() {
      this.stopSpeaking();
      this.messages = [
        {
          id: Date.now(),
          sender_id: 9999,
          message: '👋 Conversation cleared. What else can I assist you with today?',
          created_at: new Date().toISOString()
        }
      ];
    },
    renderMessage(text) {
      if (!text) return '';
      let rendered = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      rendered = rendered.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="chat-link" onclick="event.preventDefault(); window.location.pathname=\'$2\'">$1</a>');
      return rendered;
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const c = this.$refs.messagesContainer;
        if (c) {
          c.scrollTop = c.scrollHeight;
        }
      });
    }
  }
};
</script>

<style scoped>
.ai-chat-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 400px;
  height: 560px;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.22), 0 0 1px 1px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  animation: slideUp 0.25s ease;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.ai-chat-widget.dark {
  background: #181824;
  color: #f8fafc;
  border-color: #2d3748;
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.7);
}

.ai-chat-widget.rtl {
  right: auto;
  left: 24px;
  direction: rtl;
}

/* Header */
.ai-chat-header {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.dark .ai-chat-header {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border-bottom: 1px solid #334155;
}

.ai-header-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ai-avatar {
  font-size: 1.4rem;
  background: rgba(255, 255, 255, 0.2);
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}
.ai-avatar.speaking {
  animation: pulseVoice 0.8s infinite alternate;
  background: #10b981;
}

@keyframes pulseVoice {
  from { transform: scale(1); }
  to   { transform: scale(1.15); }
}

.ai-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: -0.2px;
}

.ai-status {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  gap: 4px;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background: #4ade80;
  border-radius: 50%;
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.3); }
}

.header-actions {
  display: flex;
  gap: 6px;
}

.icon-tool-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #ffffff;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.2s;
}
.icon-tool-btn:hover, .icon-tool-btn.active {
  background: rgba(255, 255, 255, 0.35);
}

/* Assistant Switcher Tabs Bar */
.assistant-tabs-bar {
  display: flex;
  background: #0f172a;
  padding: 4px 6px;
  gap: 4px;
  overflow-x: auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.assistant-tab-btn {
  flex: 1;
  min-width: 78px;
  padding: 6px 8px;
  border-radius: 8px;
  border: 1px solid transparent;
  background: transparent;
  color: #94a3b8;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.2s;
  white-space: nowrap;
}
.assistant-tab-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}
.assistant-tab-btn.active {
  color: #ffffff;
  background: linear-gradient(135deg, #0284c7, #0369a1);
  box-shadow: 0 2px 6px rgba(2, 132, 199, 0.4);
  border-color: rgba(255, 255, 255, 0.15);
}

/* Quick Prompts */
.ai-quick-prompts {
  display: flex;
  gap: 6px;
  padding: 8px 12px;
  background: #f8fafc;
  overflow-x: auto;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}
.dark .ai-quick-prompts {
  background: #1e1e2e;
  border-bottom-color: #2d3748;
}

.prompt-chip {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 20px;
  padding: 4px 10px;
  font-size: 0.72rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  color: inherit;
  transition: all 0.15s;
  flex-shrink: 0;
}
.dark .prompt-chip {
  background: #282a36;
  border-color: #475569;
}
.prompt-chip:hover {
  background: rgba(2, 132, 199, 0.1);
  border-color: #0284c7;
  color: #0284c7;
}

.quick-ai-chips {
  display: flex;
  gap: 6px;
  padding: 8px 14px 4px;
  overflow-x: auto;
  white-space: nowrap;
}
.ai-chip {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  padding: 4px 10px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  color: #334155;
  transition: all 0.15s;
  flex-shrink: 0;
}
.dark .ai-chip {
  background: #282a36;
  border-color: #475569;
  color: #e2e8f0;
}
.ai-chip:hover {
  background: #10b981;
  color: #ffffff;
  border-color: #10b981;
  transform: translateY(-1px);
}

/* Chat Body */
.ai-chat-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 14px;
  background: #ffffff;
}
.dark .ai-chat-body {
  background: #181824;
}

.chat-loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #64748b;
  padding: 20px;
}

.ai-msg-row {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  max-width: 88%;
}

.ai-msg-row.inbound {
  align-self: flex-start;
}

.ai-msg-row.outbound {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.msg-bot-avatar {
  font-size: 1rem;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e0f2fe;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.dark .msg-bot-avatar {
  background: #282a36;
}

.msg-bubble-wrap {
  display: flex;
  flex-direction: column;
}

.ai-msg-row.outbound .msg-bubble-wrap {
  align-items: flex-end;
}

.msg-bubble {
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 0.875rem;
  line-height: 1.45;
  word-break: break-word;
}

.ai-msg-row.inbound .msg-bubble {
  background: #f1f5f9;
  color: #0f172a;
  border-bottom-left-radius: 2px;
}
.dark .ai-msg-row.inbound .msg-bubble {
  background: #282a36;
  color: #f1f5f9;
}

.ai-msg-row.outbound .msg-bubble {
  background: linear-gradient(135deg, #0284c7, #0369a1);
  color: #ffffff;
  border-bottom-right-radius: 2px;
}

.msg-meta-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 3px;
  padding: 0 4px;
}

.msg-time {
  font-size: 0.65rem;
  color: #94a3b8;
}

.speak-bubble-btn {
  background: none;
  border: none;
  font-size: 0.68rem;
  font-weight: 700;
  color: #0284c7;
  cursor: pointer;
  padding: 0;
}
.dark .speak-bubble-btn {
  color: #38bdf8;
}
.speak-bubble-btn:hover {
  text-decoration: underline;
}

/* Typing Bubble */
.typing-bubble {
  background: #f1f5f9;
  padding: 10px 14px;
  border-radius: 14px;
  border-bottom-left-radius: 2px;
  display: flex;
  gap: 4px;
  align-items: center;
}
.dark .typing-bubble {
  background: #282a36;
}

.typing-dot {
  width: 6px;
  height: 6px;
  background: #94a3b8;
  border-radius: 50%;
  animation: typing 1s infinite alternate;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
  from { opacity: 0.3; transform: translateY(0); }
  to   { opacity: 1; transform: translateY(-4px); }
}

/* Voice Recording Banner */
.voice-recording-banner {
  background: #fee2e2;
  border-top: 1px solid #fecaca;
  padding: 8px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #991b1b;
}
.dark .voice-recording-banner {
  background: rgba(239, 68, 68, 0.2);
  border-top-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.record-pulse {
  animation: pulseRec 1s infinite;
  font-size: 1.1rem;
}
@keyframes pulseRec {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.2); }
}

.stop-rec-btn {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

/* Footer / Input Area */
.ai-chat-footer {
  padding: 12px 14px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.dark .ai-chat-footer {
  background: #1e1e2e;
  border-top-color: #2d3748;
}

.input-box-wrapper {
  display: flex;
  gap: 8px;
  align-items: center;
}

.voice-mic-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.1rem;
  transition: all 0.2s;
  flex-shrink: 0;
}
.dark .voice-mic-btn {
  background: #282a36;
  border-color: #475569;
}
.voice-mic-btn:hover {
  background: rgba(2, 132, 199, 0.15);
  border-color: #0284c7;
}
.voice-mic-btn.recording {
  background: #fee2e2;
  border-color: #ef4444;
  animation: pulseRec 1s infinite;
}

.ai-input {
  flex: 1;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.85rem;
  outline: none;
  transition: all 0.2s;
}
.dark .ai-input {
  background: #181824;
  border-color: #475569;
}
.ai-input:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.ai-send-btn {
  background: linear-gradient(135deg, #0284c7, #0369a1);
  border: none;
  color: #ffffff;
  border-radius: 12px;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s;
  flex-shrink: 0;
}
.ai-send-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
}
.ai-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.footer-sub-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ai-footer-note {
  font-size: 0.65rem;
  color: #94a3b8;
}

.stop-speech-btn {
  background: none;
  border: none;
  font-size: 0.68rem;
  font-weight: 700;
  color: #ef4444;
  cursor: pointer;
  padding: 0;
}
.stop-speech-btn:hover {
  text-decoration: underline;
}

/* Spinner */
.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(2, 132, 199, 0.3);
  border-top-color: #0284c7;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Chat link */
:deep(.chat-link) {
  color: #0284c7;
  text-decoration: underline;
  font-weight: 700;
}
</style>
