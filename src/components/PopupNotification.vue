<template>
  <div class="popup-overlay" v-if="showPopup">
    <div class="popup">
      <p>{{ $t('popup.message') }}</p>
      <div class="popup-actions">
        <button @click="accept">{{ $t('popup.accept') }}</button>
        <button @click="reject">{{ $t('popup.reject') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PopupNotification',
  data() {
    return {
      showPopup: true
    };
  },
  methods: {
    accept() {
      this.showPopup = false;
      this.$emit('accept');
    }
    ,  computed: {
    popupDirection() {
      return this.$i18n.locale === 'ar' ? 'popup-rtl' : 'popup-ltr';
    }
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
  flex-wrap: wrap;
}
  
/* Align buttons based on locale */
.popup-ltr .popup-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.popup-rtl .popup-actions {
  display: flex;
  justify-content: flex-start;
  margin-top: 10px;
}

.popup-actions button {
  background-color: #275559;
  border: none;
  color: #fff;
  padding: 8px 16px;
  margin-left: 10px;
  cursor: pointer;
  border-radius: 4px;
}
</style>
