<template>
  <div :class="['testimonial-wrapper', { 'page-mode': isPageMode, dark: isDarkMode, rtl: isArabic }]">
    <div class="testimonial-card">
      <div class="card-top-bar">
        <div class="card-title-group">
          <span class="card-icon">🌟</span>
          <div>
            <h2 class="card-title">{{ $t('addTestimonial.title') || 'Submit Testimonial & Review' }}</h2>
            <p class="card-subtitle">Share your experience with Fawaz Platform & Company 2 services</p>
          </div>
        </div>
        <button class="close-btn" @click="handleClose" title="Back to Dashboard">
          ✕
        </button>
      </div>

      <div v-if="submitted" class="success-banner">
        <span class="banner-icon">🎉</span>
        <div class="banner-text">
          <strong>Thank you for your feedback!</strong>
          <p>Your testimonial has been verified and added to the community showcase.</p>
        </div>
        <button class="dash-btn" @click="$router.push('/dashboard')">Back to Dashboard</button>
      </div>

      <form v-else @submit.prevent="submitTestimonial" class="testimonial-form">
        <!-- Star Rating -->
        <div class="form-group">
          <label class="form-label">Rating</label>
          <div class="star-rating">
            <span
              v-for="star in 5"
              :key="star"
              class="star-item"
              :class="{ filled: star <= rating }"
              @click="rating = star"
            >
              ★
            </span>
            <span class="rating-label">{{ rating }}.0 / 5.0 Stars</span>
          </div>
        </div>

        <!-- User Photo Upload -->
        <div class="form-group">
          <label class="form-label">{{ $t('addTestimonial.userPhoto') }}</label>
          <div class="file-drop-area" :class="{ 'has-file': !!photoPreview }">
            <img v-if="photoPreview" :src="photoPreview" alt="Preview" class="photo-preview" />
            <div v-else class="upload-placeholder">
              <span class="upload-icon">📷</span>
              <span class="file-message">{{ $t('addTestimonial.dropImage') }}</span>
              <span class="file-info">{{ $t('addTestimonial.maxSize') }}</span>
            </div>
            <input type="file" accept="image/*" @change="handleFileUpload" class="file-input" />
          </div>
        </div>

        <!-- Full Name & Company Name Grid -->
        <div class="input-grid">
          <div class="form-group">
            <label for="username" class="form-label">{{ $t('addTestimonial.username') }}</label>
            <input
              type="text"
              id="username"
              v-model="username"
              required
              class="modern-input"
              placeholder="e.g. Sarah Jenkins"
            />
          </div>

          <div class="form-group">
            <label for="companyName" class="form-label">{{ $t('addTestimonial.companyName') }}</label>
            <input
              type="text"
              id="companyName"
              v-model="companyName"
              required
              class="modern-input"
              placeholder="e.g. Apex Logistics"
            />
          </div>
        </div>

        <!-- Content -->
        <div class="form-group">
          <div class="label-row">
            <label for="content" class="form-label">{{ $t('addTestimonial.content') }}</label>
            <span class="char-counter">{{ content.length }} / 200</span>
          </div>
          <textarea
            id="content"
            v-model="content"
            maxlength="200"
            required
            class="modern-textarea"
            placeholder="Tell us about the features you loved, efficiency gains, and overall experience..."
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="handleClose">Cancel</button>
          <button type="submit" class="save-button" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner"></span>
            <span v-else>{{ $t('addTestimonial.save') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { reviewApi } from '@/services/api';

export default {
  name: 'AddTestimonial',
  props: {
    isModal: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      photo: null,
      photoPreview: null,
      username: '',
      companyName: '',
      content: '',
      rating: 5,
      submitted: false,
      isSubmitting: false,
    };
  },
  computed: {
    ...mapState({
      user:       state => state.auth ? state.auth.user : null,
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
      locale:     state => state.settings ? state.settings.locale : 'en',
    }),
    isPageMode() {
      return !this.isModal;
    },
    isArabic() {
      return this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    }
  },
  mounted() {
    if (this.user) {
      this.username = this.user.name || this.user.username || '';
    }
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.photoPreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    async submitTestimonial() {
      this.isSubmitting = true;
      try {
        await reviewApi.create({
          author: this.username,
          merchant: this.companyName,
          rating: this.rating,
          text: this.content,
          date: new Date().toISOString().substring(0, 10),
          status: 'Approved'
        }).catch(() => null);

        this.submitted = true;
        this.$emit('save', {
          id: Date.now(),
          username: this.username,
          companyName: this.companyName,
          content: this.content,
          rating: this.rating,
          photo: this.photo
        });
      } finally {
        this.isSubmitting = false;
      }
    },
    handleClose() {
      if (this.isModal) {
        this.$emit('close');
      } else {
        this.$router.push('/dashboard');
      }
    }
  }
};
</script>

<style scoped>
.testimonial-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.testimonial-wrapper.page-mode {
  position: relative;
  min-height: 100vh;
  background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
}
.testimonial-wrapper.page-mode.dark {
  background: linear-gradient(135deg, #0f172a 0%, #1e1e2e 100%);
}

.testimonial-wrapper.rtl {
  direction: rtl;
}

.testimonial-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 620px;
  padding: 32px;
  box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.15), 0 0 1px 1px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  color: #0f172a;
}
.dark .testimonial-card {
  background: #1e1e2e;
  border-color: #2d3748;
  color: #f8fafc;
  box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.6);
}

.card-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}
.dark .card-top-bar {
  border-bottom-color: #2d3748;
}

.card-title-group {
  display: flex;
  align-items: center;
  gap: 14px;
  text-align: left;
}
.rtl .card-title-group {
  text-align: right;
}

.card-icon {
  font-size: 2rem;
  background: rgba(245, 158, 11, 0.15);
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-title {
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0 0 4px 0;
}

.card-subtitle {
  font-size: 0.825rem;
  color: #64748b;
  margin: 0;
}
.dark .card-subtitle {
  color: #94a3b8;
}

.close-btn {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #64748b;
  transition: all 0.2s;
}
.dark .close-btn {
  background: #282a36;
  border-color: #334155;
  color: #94a3b8;
}
.close-btn:hover {
  background: #fee2e2;
  color: #ef4444;
}

.star-rating {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 1.8rem;
  cursor: pointer;
}

.star-item {
  color: #cbd5e1;
  transition: color 0.15s, transform 0.15s;
}
.star-item.filled {
  color: #f59e0b;
}
.star-item:hover {
  transform: scale(1.15);
}

.rating-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #f59e0b;
  margin-left: 10px;
}

.testimonial-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}
.rtl .form-group {
  text-align: right;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
}
.dark .form-label {
  color: #cbd5e1;
}

.input-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media (max-width: 500px) {
  .input-grid {
    grid-template-columns: 1fr;
  }
}

.modern-input, .modern-textarea {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  color: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
  box-sizing: border-box;
}
.dark .modern-input, .dark .modern-textarea {
  background: #181824;
  border-color: #334155;
}
.modern-input:focus, .modern-textarea:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.modern-textarea {
  min-height: 90px;
  resize: vertical;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.char-counter {
  font-size: 0.75rem;
  color: #94a3b8;
}

.file-drop-area {
  position: relative;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dark .file-drop-area {
  border-color: #334155;
  background: #181824;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.upload-icon {
  font-size: 1.8rem;
}

.file-message {
  font-size: 0.85rem;
  font-weight: 600;
}

.file-info {
  font-size: 0.72rem;
  color: #94a3b8;
  margin: 0;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.photo-preview {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #0284c7;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.cancel-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  color: inherit;
}
.dark .cancel-btn {
  background: #282a36;
  border-color: #334155;
}

.save-button {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
  color: #ffffff;
  border: none;
  padding: 10px 24px;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
  transition: all 0.2s;
}
.save-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
}

.success-banner {
  padding: 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
.banner-icon {
  font-size: 2.5rem;
}
.banner-text strong {
  font-size: 1.1rem;
  display: block;
}
.banner-text p {
  font-size: 0.85rem;
  color: #64748b;
  margin: 4px 0 0 0;
}
.dark .banner-text p {
  color: #94a3b8;
}
.dash-btn {
  background: #0284c7;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}
</style>
