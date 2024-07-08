<template>
  <div class="modal-overlay">
    <div class="modal">
      <button class="close-button" @click="$emit('close')">×</button>
      <h2>Add New Testimonial</h2>
      <form @submit.prevent="submitTestimonial">
        <div class="form-group">
          <label>User Photo</label>
          <div class="file-drop-area" @click="triggerFileInput">
            <span class="file-message">Drop Your Image Here Or Browse</span>
            <input type="file" ref="fileInput" @change="handleFileUpload" />
            <p>Max Size 5 MB, 35*35 Pixel Supported Format .Jpg, .Png</p>
          </div>
        </div>
        <div class="form-group">
          <label for="username">User Name</label>
          <input type="text" id="username" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="companyName">Company Name</label>
          <input type="text" id="companyName" v-model="companyName" required />
        </div>
        <div class="form-group">
          <label for="content">Content</label>
          <textarea id="content" v-model="content" maxlength="100" required></textarea>
          <div class="character-count">{{ content.length }}/100</div>
        </div>
        <button type="submit" class="save-button">Save</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddTestimonial',
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
      console.log(this.photo); // To check if the file is being uploaded
    },
    submitTestimonial() {
      if (!this.photo) {
        alert('Please select a photo');
        return;
      }
      const testimonial = {
        id: Date.now(),
        username: this.username,
        companyName: this.companyName,
        content: this.content,
        photo: this.photo
      };
      this.$emit('save', testimonial);
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
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

.modal {
  background-color: #2c2c2c;
  padding: 20px;
  border-radius: 10px;
  width: 700px;
  position: relative;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.close-button {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #fff;
}

h2 {
  color: #fff;
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #fff;
}

.file-drop-area {
  border: 2px dashed #666;
  padding: 20px;
  text-align: center;
  color: #fff;
  cursor: pointer;
  position: relative;
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
}

.form-group input,
.form-group textarea {
  width: 80%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background: #333;
  color: #fff;
}

.character-count {
  text-align: right;
  font-size: 0.875em;
  color: #666;
}

.save-button {
  width: 100%;
  padding: 10px;
  background-color: #ffcc00;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  color: #333;
  font-weight: bold;
}
</style>
