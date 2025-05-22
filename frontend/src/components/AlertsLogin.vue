<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  duration: {
    type: Number,
    default: 2000,
  },
  show: {
    type: Boolean,
    default: true,
  },
});

const emits = defineEmits(["close"]);
const isVisible = ref(false);

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 10);

  if (props.duration) {
    setTimeout(() => {
      isVisible.value = false;
      emits("close");
    }, props.duration + 10);
  }
});
</script>

<template>
  <Transition name="slide-fade">
    <div v-if="isVisible" class="notification">
      {{ message }}
    </div>
  </Transition>
</template>

<style scoped>
.notification {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  background: #1b130b;
  border-radius: 12px;
  font-weight: 600;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  text-align: center;
  color: #f80;
  font-family: "Inter";
  z-index: 1000;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateX(-50%) translateY(-10px);
  opacity: 0;
}

.slide-fade-enter-to {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

.slide-fade-leave-to {
  transform: translateX(-50%) translateY(-10px);
  opacity: 0;
}
</style>
