<template>
  <div class="popup-overlay" v-if="showPopup">
    <div class="popup">
      <p>{{ message }}</p>
      <div class="popup-actions">
        <button @click="accept">{{ acceptText }}</button>
        <button @click="reject">{{ rejectText }}</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PopupNotification',
  props: {
    message: {
      type: String,
      default: 'Do you accept the terms and conditions?'
    },
    acceptText: {
      type: String,
      default: 'Accept'
    },
    rejectText: {
      type: String,
      default: 'Reject'
    }
  },
  data() {
    return {
      showPopup: true
    };
  },
  methods: {
    accept() {
      this.showPopup = false;
      this.$emit('accept');
    },
    reject() {
      this.showPopup = false;
      this.$emit('reject');
    }
  }
};
</script>

<style scoped>
.popup-overlay {
  position: fixed;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.8);
  color: #fff;
  padding: 10px 20px;
  border-radius: 5px;
  z-index: 1000;
}

.popup {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.popup-actions button {
  background-color: #ffcc00;
  border: none;
  color: #333;
  padding: 5px 10px;
  margin-left: 10px;
  cursor: pointer;
  border-radius: 4px;
}
</style>
