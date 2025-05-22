<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter } from "vue-router";
import { auth } from "@/api/auth";
import CreateCollection from "@/components/CreateCollection.vue";
import AlertsLogin from "@/components/AlertsLogin.vue";

const router = useRouter();
const user = ref(null);
const currentCollection = ref(null);
const hasCollection = ref(false);
const isCreatingCollection = ref(false);
const showLoginAlert = ref(false);
const showQRPopup = ref(false);
const qrCodeUrl = ref(null);
const notificationMessage = ref("");
const showNotification = ref(false);

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (!storedUser) {
    router.push("/login");
    return;
  }
  user.value = JSON.parse(storedUser);
  showLoginAlert.value = true;
  fetchCollection();
});

const fetchCollection = async () => {
  try {
    const collection = await auth.getCollection();
    if (collection) {
      currentCollection.value = collection;
      hasCollection.value = true;
    }
  } catch (error) {
    console.error("Ошибка загрузки сбора:", error);
  }
};

const createCollection = async (newCollection) => {
  try {
    const createdCollection = await auth.createCollection(
      newCollection.title,
      newCollection.description,
      newCollection.goal
    );
    currentCollection.value = createdCollection;
    hasCollection.value = true;
    isCreatingCollection.value = false;
    showNotification.value = true;
    notificationMessage.value = "Сбор успешно создан";
    setTimeout(() => (showNotification.value = false), 3000);
  } catch (error) {
    console.error("Ошибка при создании сбора:", error);
    showNotification.value = true;
    notificationMessage.value = "Ошибка при создании сбора";
    setTimeout(() => (showNotification.value = false), 3000);
  }
};

const deleteCollection = async () => {
  try {
    await auth.deleteCollection(currentCollection.value.id);
    currentCollection.value = null;
    hasCollection.value = false;
    qrCodeUrl.value = null;
    showNotification.value = true;
    notificationMessage.value = "сбор удален";
    setTimeout(() => (showNotification.value = false), 3000);
  } catch (error) {
    console.error("Ошибка при удалении сбора:", error);
    showNotification.value = true;
    notificationMessage.value = "ошибка при удалении сбора";
    setTimeout(() => (showNotification.value = false), 3000);
  }
};

const getQRCode = () => {
  if (currentCollection.value) {
    const collectionId = currentCollection.value.id;
    qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(
      `http://localhost:5173/collection/${collectionId}`
    )}`;
    showQRPopup.value = true;
  }
};

const copyLink = () => {
  if (currentCollection.value) {
    const collectionLink = `http://localhost:5173/collection/${currentCollection.value.id}`;
    navigator.clipboard.writeText(collectionLink).then(() => {
      showNotification.value = true;
      notificationMessage.value = "ссылка скопирована";
      setTimeout(() => (showNotification.value = false), 3000);
    });
  }
};

const closeQRPopup = (event) => {
  if (!event.target.closest(".qr-popup-content")) {
    showQRPopup.value = false;
  }
};

const isGoalReached = computed(() => {
  return (
    currentCollection.value &&
    currentCollection.value.collected >= currentCollection.value.goal
  );
});

const handleLogout = () => {
  localStorage.removeItem("user");
  router.push("/login");
};
</script>

<template>
  <main>
    <div class="container">
      <AlertsLogin
        v-if="showLoginAlert"
        message="вход успешно выполнен"
        :duration="2000"
        @close="handleAlertClose"
      />
      <AlertsLogin
        v-if="showNotification"
        :message="notificationMessage"
        :duration="3000"
        @close="showNotification = false"
      />
      <div class="header-wrapper animated fade-in">
        <div class="card-title">личный кабинет</div>
      </div>

      <div class="user-info animated slide-up">
        <div class="user-info__card">
          <div class="user-info__header">
            <div class="user-info__text">
              <p class="user-info__collecting">
                добро пожаловать, {{ user?.name || user?.email }}
              </p>
              <div class="user-info__goal">{{ user?.email }}</div>
            </div>
            <div class="user-info__avatar">
              <img
                :src="user?.avatar || '/src/assets/img/avatars.jpg'"
                alt="User avatar"
              />
            </div>
          </div>
          <button
            @click="handleLogout"
            class="action-button action-button--secondary animated fade-in"
          >
            выйти
          </button>
        </div>
      </div>

      <div
        v-if="hasCollection && !isCreatingCollection"
        class="collection-info animated slide-up"
      >
        <div class="user-info__card">
          <div class="user-info__header">
            <div class="user-info__text">
              <p class="user-info__collecting">{{ currentCollection.title }}</p>
              <div class="user-info__goal">
                {{ currentCollection.description }}
              </div>
            </div>
          </div>

          <div class="user-info__progress">
            <div class="user-info__progress-text">
              Собрано {{ currentCollection.collected.toLocaleString() }} из
              {{ currentCollection.goal.toLocaleString() }} ₽
            </div>
            <div class="user-info__progress-bar">
              <div
                class="user-info__progress-fill"
                :style="{
                  width: `${
                    (currentCollection.collected / currentCollection.goal) * 100
                  }%`,
                }"
              ></div>
            </div>
          </div>

          <div class="action-buttons">
            <button
              v-if="!isGoalReached"
              class="action-button action-button--primary animated fade-in"
              @click="getQRCode"
            >
              получить QR-код
            </button>
            <button
              v-if="isGoalReached"
              class="action-button action-button--primary animated fade-in"
              disabled
            >
              цель достигнута
            </button>
            <button
              class="action-button action-button--secondary animated fade-in"
              @click="deleteCollection"
            >
              удалить сбор
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="!hasCollection && !isCreatingCollection"
        class="animated slide-up"
      >
        <button
          class="action-button action-button--primary animated fade-in"
          @click="isCreatingCollection = true"
        >
          создать сбор
        </button>
      </div>

      <div
        v-if="isCreatingCollection"
        class="collection-container animated slide-up"
      >
        <CreateCollection
          @create-collection="createCollection"
          @cancel-create="isCreatingCollection = false"
        />
      </div>

      <div
        v-if="showQRPopup"
        class="qr-overlay animated fade-in"
        @click="closeQRPopup"
      >
        <div class="qr-popup-content">
          <img :src="qrCodeUrl" alt="QR Code" />
          <button class="copy-link-button animated fade-in" @click="copyLink">
            Скопировать ссылку
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.container {
  padding: 0 16px;
  position: relative;
}

.card-title {
  font-family: "Inter";
  width: 343px;
  color: #fff;
  font-size: 50px;
  font-weight: 700;
  line-height: 50px;
  letter-spacing: -0.75px;
}

.user-info__card {
  margin-top: 32px;
  margin-bottom: 32px;
  padding: 16px;
  background-color: #303030;
  border-radius: 12px;
}

.user-info__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.user-info__text {
  flex-grow: 1;
}

.user-info__collecting {
  font-family: "Inter";
  font-weight: 600;
  font-size: 14px;
  line-height: 114%;
  letter-spacing: -0.01em;
  color: #8e8e8e;
}

.user-info__goal {
  color: #fff;
  font-family: "Inter";
  font-weight: 600;
  font-size: 20px;
  line-height: 140%;
  letter-spacing: -0.01em;
  color: #fff;
  gap: 8px;
}

.user-info__avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  overflow: hidden;
}

.user-info__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info__progress {
  padding-top: 12px;
  border-top: 1px solid #4b4b4b;
}

.user-info__progress-text {
  color: #fff;
  font-family: "Inter";
  font-weight: 600;
  font-size: 14px;
  line-height: 114%;
  letter-spacing: -0.01em;
  color: #fff;
  margin-bottom: 8px;
}

.user-info__progress-bar {
  height: 8px;
  background: linear-gradient(90deg, #f2eaea 0%, #4f4d4d 100%);
  border-radius: 4px;
  overflow: hidden;
}

.user-info__progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #f80 0%, #ef5000 100%);
  border-radius: 4px;
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

.action-button--primary:disabled {
  background: #1a1a1a;
  box-shadow: none;
  cursor: not-allowed;
}

.action-button--secondary {
  color: #ffffff;
  border-radius: 12px;
  padding: 16px;
  background-color: #1a1a1a;
}

.collection-container {
  margin-top: 32px;
}

.qr-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.qr-popup-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #303030;
  padding: 32px;
  border-radius: 12px;
  text-align: center;
  position: relative;
}

.qr-popup-content img {
  width: 180px;
  height: 180px;
  border: 4px solid white;
  border-radius: 12px;
}

.copy-link-button {
  margin-top: 24px;
  font-family: "Inter";
  font-weight: 600;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  text-align: center;
  border: none;
  cursor: pointer;
  color: #ffffff;
  background: #1a1a1a;
  border-radius: 12px;
  padding: 12px;
}

.animated {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}

.fade-in {
  animation-name: fadeIn;
}

.slide-up {
  animation-name: slideUp;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.qr-overlay.animated {
  animation-delay: 0.1s;
}

.alerts-login {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1100;
}

.goal-reached-message {
  margin-top: 16px;
  padding: 12px;
  background-color: #4caf50;
  border-radius: 12px;
  text-align: center;
}

.goal-reached-text {
  font-family: "Inter";
  font-weight: 600;
  font-size: 16px;
  color: #fff;
  margin: 0;
}
</style>
