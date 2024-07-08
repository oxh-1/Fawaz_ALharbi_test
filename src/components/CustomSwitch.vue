<template>
  <label class="switch">
    <input type="checkbox" :checked="localValue" @change="updateValue" />
    <span class="slider"></span>
  </label>
</template>

<script>
export default {
  name: 'CustomSwitch',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      localValue: this.modelValue
    };
  },
  watch: {
    modelValue(newValue) {
      this.localValue = newValue;
    }
  },
  methods: {
    updateValue(event) {
      this.localValue = event.target.checked;
      this.$emit('update:modelValue', this.localValue);
    }
  }
};
</script>

<style scoped>
.switch {
  position: relative;
  display: inline-block;
  width: 51px;
  height: 31px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 31px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 23px;
  width: 23px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #6200ea;
}

input:checked + .slider:before {
  transform: translateX(20px);
}
</style>
