<template>
  <div :class="['modal-overlay', { dark: isDarkMode }]">
    <div :class="['modal', { dark: isDarkMode }]">
      <button class="close-button" @click="$emit('close')">
        <div class="square"></div>
        <div class="x"></div>
      </button>
      <h2 class="modal-title">{{ $t('addTestimonial.title') }}</h2>
      <form @submit.prevent="submitTestimonial">
        <div class="form-group">
          <label>{{ $t('addTestimonial.userPhoto') }}</label>
          <div :class="['file-drop-area', { dark: isDarkMode }]">
            <img src="@/assets/Gittax/download.png" alt="Gittax Logo" class="logo" />
            <span class="file-message">{{ $t('addTestimonial.dropImage') }}</span>
            <input type="file" @change="handleFileUpload" />
            <p class="file-info">{{ $t('addTestimonial.maxSize') }}</p>
          </div>
        </div>
        <div class="form-group">
          <label for="username">{{ $t('addTestimonial.username') }}</label>
          <input type="text" id="username" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="companyName">{{ $t('addTestimonial.companyName') }}</label>
          <input type="text" id="companyName" v-model="companyName" required />
        </div>
        <div class="form-group">
          <label for="content">{{ $t('addTestimonial.content') }}</label>
          <div class="textarea-container">
            <textarea id="content" v-model="content" maxlength="100" required></textarea>
            <div class="character-count"><span class="count">{{ content.length }}</span>/100</div>
          </div>
        </div>
        <button type="submit" class="save-button">{{ $t('addTestimonial.save') }}</button>
      </form>
    </div>
  </div>
</template>
<script>
export default {
  name: 'AddTestimonial',
  props: {
    isDarkMode: {
      type: Boolean,
      default: false
    },
    isArabic: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      photo: null,
      username: '',
      companyName: '',
      content: ''
    };
  },
  methods: {
    handleFileUpload(event) {
      this.photo = event.target.files[0];
    },
    submitTestimonial() {
      const testimonial = {
        id: Date.now(),
        username: this.username,
        companyName: this.companyName,
        content: this.content,
        photo: this.photo
      };
      this.$emit('save', testimonial);
    }
  }
};
</script>
<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-overlay.dark {
  background-color: rgba(0, 0, 0, 0.8);
}

.modal {
  background-color: #2c2c2c;
  padding: 20px;
  border-radius: 10px;
  width: 40%;
  height: 82%;
  position: relative;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow-y: auto;
}

.modal.dark {
  background-color: #1c1c1c;
}

.close-button {
  position: absolute;
  top: 40px;
  right: 10px;
  background: #cccccc;
  border: 2px solid #000000; /* Black stroke */
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}

.close-button .square {
  width: 7px;
  height: 7px;
  border: 2px solid #000000;
  background: transparent;
  position: absolute;
  top: 13px;
  right: 13px;
}

.close-button .x {
  position: relative;
  width: 10px;
  height: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.close-button .x::before,
.close-button .x::after {
  content: '';
  position: absolute;
  width: 14px;
  height: 2px;
  background-color: #FFFFFF;
}

.close-button .x::before {
  transform: rotate(45deg);
}

.close-button .x::after {
  transform: rotate(-45deg);
}

.modal-title {
  display: inline-block;
  color: #FFFFFF;
  /* Up shadow */
  margin-bottom: 50px;
  font-size: 24px;
  width: calc(100% - 60px); /* Adjusted width to fit with the close button */
  line-height: 40px; /* Match the close button height */
}

.form-group {
  width: 95%;
  margin-bottom: 15px;
  display: center;
  flex-direction: column;
  justify-content: center;
}

.form-group label {
  margin-bottom: 5px;
  color: #fff;
  font-size: 16px;
}

.file-drop-area {
    display: center;
  flex-direction: column;
  justify-content: center;
  width: 100%;
  height: 100%;
  border: 0px dashed #fff;
    background: url('@/assets/Gittax/cut.png') center center / cover no-repeat;

  padding: 20px;
  text-align: center;
  color: #fff;
  cursor: pointer;
  position: relative;
  margin-bottom: 15px;
}

.file-drop-area.dark {
  border: 1px dashed #888;
  color: #888;
}

.file-drop-area input[type="file"] {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.file-message {
  display: block;
  font-size: 14px;
  margin-bottom: 10px;
  color: #FAFAFA;
}

.file-info {
  font-size: 14px;
  color: #FAFAFA;
}

.uploaded-photo {
  width: 53px;
  height: 53px;
  border-radius: 50%;
  margin: 0 auto;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: none;
  resize: none;
  border-radius: 5px;
  background: #5f5f5f;
  color: #fff;
}

.form-group input.dark,
.form-group textarea.dark {
  background: #3f3f3f;
  color: #ccc;
}

.textarea-container {
  position: relative;
  width: 100%;
}

.textarea-container textarea {
  height: 150%;
}

.character-count {
  text-align: right;
  font-size: 0.875em;
  color: #FFFFFF;
  position: absolute;
  bottom: 5px;
  right: 10px;
}

.character-count .count {
  color: #00aaff;
}

form {
  width: 100%;
}

.save-button {
  width: 13%;
  text-align: center;
  padding: 10px;
  background-color: #00aaff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  color: #333;
  font-weight: bold;
  margin-top: 15px; /* Add margin to create space between textarea and button */
margin-left: 80%;

}

.save-button.dark {
  background-color: #00aaff;
}
</style>
