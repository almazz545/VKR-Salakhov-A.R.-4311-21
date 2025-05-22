<script setup>
import { ref } from "vue";

const title = ref("");
const description = ref("");
const goal = ref("");

const emit = defineEmits(["create-collection", "cancel-create"]);

const handleSubmit = () => {
  if (title.value && description.value && goal.value) {
    const newCollection = {
      title: title.value,
      description: description.value,
      goal: parseInt(goal.value),
      collected: 0,
    };
    emit("create-collection", newCollection);
  } else {
    alert("Пожалуйста, заполните все поля");
  }
};

const handleCancel = () => {
  emit("cancel-create");
};
</script>

<template>
  <div class="collection-form">
    <div class="input-container">
      <div class="input-wrapper">
        <input
          v-model="title"
          type="text"
          class="tip-selector__input"
          placeholder="твое имя"
        />
      </div>
      <div class="input-wrapper">
        <input
          v-model="description"
          type="text"
          class="tip-selector__input"
          placeholder="на что идет сбор"
        />
      </div>
      <div class="input-wrapper">
        <input
          v-model="goal"
          type="number"
          class="tip-selector__input"
          placeholder="сумма сбора"
        />
      </div>
    </div>

    <div class="action-buttons">
      <button
        class="action-button action-button--primary"
        @click="handleSubmit"
      >
        Сохранить
      </button>
      <button
        class="action-button action-button--secondary"
        @click="handleCancel"
      >
        Отменить
      </button>
    </div>
  </div>
</template>

<style scoped>
.collection-form {
  margin-top: 32px;
}

.input-container {
  background-color: #303030;
  border-radius: 12px;
  padding: 16px;
  margin-top: 16px;
}

.input-wrapper {
  margin-bottom: 16px;
}

.tip-selector__input {
  width: 100%;
  height: 48px;
  padding: 12px 16px;
  border: none;
  color: #fff;
  font-family: "Inter";
  font-size: 16px;
  font-weight: 500;
  outline: none;
  transition: background-color 0.2s ease;
  box-sizing: border-box;
  background-color: #444;
  border-radius: 10px;
}

.tip-selector__input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 24px;
}

.action-button {
  width: 100%;
  font-family: "Inter";
  font-weight: 600;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  text-align: center;
  border: none;
  cursor: pointer;
}

.action-button--primary {
  color: #ffffff;
  box-shadow: inset 0 0 5px 0 rgba(255, 255, 255, 0.6),
    0 2px 5px 0 rgba(241, 89, 10, 0.12);
  background: linear-gradient(180deg, #f80 0%, #ef5000 100%),
    radial-gradient(
      94.42% 96.86% at 50.18% 0%,
      rgba(255, 255, 255, 0.4) 0%,
      rgba(255, 255, 255, 0) 100%
    );
  border-radius: 12px;
  padding: 16px;
}

.action-button--secondary {
  color: #ffffff;
  border-radius: 12px;
  padding: 16px;
  background-color: #1a1a1a;
}
</style>
