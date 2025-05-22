<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { auth } from "@/api/auth";
import AlertsLogin from "@/components/AlertsLogin.vue";

const route = useRoute();
const collectionId = ref(route.params.id);
const collection = ref(null);
const error = ref(null);
const donationAmount = ref(null);
const showNotification = ref(false);
const notificationMessage = ref("");
const orderTotal = ref(null);
const showPaymentPopup = ref(false);

const generateRandomOrderTotal = () => {
  return Math.floor(Math.random() * (8000 - 400 + 1)) + 400;
};

onMounted(async () => {
  try {
    collection.value = await auth.getCollectionById(collectionId.value);
    orderTotal.value =
      collection.value.order_total || generateRandomOrderTotal();
  } catch (err) {
    error.value = err.message || "Ошибка загрузки коллекции";
  }
});

watch(collection, (newCollection) => {
  if (newCollection && newCollection.order_total) {
    orderTotal.value = newCollection.order_total;
  }
});

const tipPercentages = [7, 10, 12];

const tips = computed(() => {
  if (!collection.value || !orderTotal.value) return [];
  return tipPercentages.map((percentage) => ({
    percentage,
    amount: Math.round((orderTotal.value * percentage) / 100),
  }));
});

const selectedTip = ref(null);
const isCustomMode = ref(false);
const customAmount = ref("");
const isCustomAmountConfirmed = ref(false);

const customTipPercentage = computed(() => {
  if (customAmount.value && orderTotal.value) {
    const percentage = (parseInt(customAmount.value) / orderTotal.value) * 100;
    return percentage.toLocaleString();
  }
  return null;
});

const handleTipSelect = (index) => {
  if (selectedTip.value === index) {
    selectedTip.value = null;
    donationAmount.value = null;
  } else {
    selectedTip.value = index;
    donationAmount.value = tips.value[index].amount;
  }
  isCustomMode.value = false;
  isCustomAmountConfirmed.value = false;
  customAmount.value = "";
};

const handleCustomClick = () => {
  if (isCustomAmountConfirmed.value) {
    isCustomMode.value = false;
    isCustomAmountConfirmed.value = false;
    customAmount.value = "";
    donationAmount.value = null;
  } else {
    isCustomMode.value = true;
    selectedTip.value = null;
    donationAmount.value = null;
  }
};

const handleCustomAmountChange = (event) => {
  customAmount.value = event.target.value.replace(/[^\d]/g, "");
};

const confirmCustomAmount = () => {
  if (customAmount.value) {
    isCustomAmountConfirmed.value = true;
    isCustomMode.value = false;
    donationAmount.value = parseInt(customAmount.value);
  }
};

const handleKeyPress = (event) => {
  if (event.key === "Enter") {
    confirmCustomAmount();
  }
};

const displayAmount = computed(() => {
  if (isCustomAmountConfirmed.value && customAmount.value) {
    const amount = parseInt(customAmount.value);
    return `${amount.toLocaleString()} ₽`;
  }
  return "ввести свою сумму";
});

const selectedTipAmount = computed(() => {
  if (selectedTip.value !== null) {
    return tips.value[selectedTip.value].amount;
  }
  if (isCustomAmountConfirmed.value && customAmount.value) {
    return parseInt(customAmount.value);
  }
  return null;
});

const makeDonation = () => {
  if (!donationAmount.value || donationAmount.value <= 0) {
    showNotification.value = true;
    notificationMessage.value = "Выберите сумму чаевых";
    setTimeout(() => (showNotification.value = false), 3000);
    return;
  }

  showPaymentPopup.value = true;
};

const confirmPayment = async () => {
  try {
    const newCollected =
      (collection.value.collected || 0) + donationAmount.value;
    const updatedCollection = await auth.updateCollection(
      collection.value.id,
      collection.value.title,
      collection.value.description,
      collection.value.goal,
      newCollected
    );
    collection.value.collected = updatedCollection.collected;

    showPaymentPopup.value = false;
    showNotification.value = true;
    notificationMessage.value = `Чаевые на ${donationAmount.value.toLocaleString()} ₽ отправлены успешно`;
    setTimeout(() => (showNotification.value = false), 3000);

    donationAmount.value = null;
    selectedTip.value = null;
    isCustomMode.value = false;
    isCustomAmountConfirmed.value = false;
    customAmount.value = "";
  } catch (error) {
    console.error("Ошибка при отправке чаевых:", error);
    showPaymentPopup.value = false;
    showNotification.value = true;
    notificationMessage.value = "Ошибка при отправке чаевых";
    setTimeout(() => (showNotification.value = false), 3000);
  }
};

const closePaymentPopup = (event) => {
  if (!event.target.closest(".payment-popup-content")) {
    showPaymentPopup.value = false;
  }
};
</script>

<template>
  <main>
    <div class="container" v-if="collection">
      <div class="header-wrapper">
        <div class="card-title">оставить чаевые</div>
        <div class="card-order_info">
          по чеку
          <span class="total-primary">{{ orderTotal.toLocaleString() }} ₽</span>
        </div>
      </div>

      <div class="tip-selector">
        <button
          v-for="(tip, index) in tips"
          :key="index"
          class="tip-selector__button tip-selector__button--percentage"
          :class="{
            'tip-selector__button--active': selectedTip === index,
            'tip-selector__button--no-hover': selectedTip === index,
          }"
          @click="handleTipSelect(index)"
        >
          <span class="tip-selector__percentage">
            {{ tip.percentage }}<span class="percentage-gray">%</span>
          </span>
          <span class="tip-selector__amount">{{ tip.amount }} ₽</span>
        </button>

        <div class="tip-selector__custom-wrapper">
          <button
            v-if="!isCustomMode"
            class="tip-selector__button tip-selector__button--custom"
            :class="{
              'tip-selector__button--active': isCustomAmountConfirmed,
              'tip-selector__button--no-hover': isCustomAmountConfirmed,
            }"
            @click="handleCustomClick"
          >
            <span v-if="!isCustomAmountConfirmed">
              ввести свою сумму <span class="percentage-gray">→</span>
            </span>
            <span v-else class="tip-selector__amount">{{ displayAmount }}</span>
          </button>

          <div v-else class="tip-selector__input-wrapper">
            <input
              type="text"
              v-model="customAmount"
              @input="handleCustomAmountChange"
              @keypress="handleKeyPress"
              class="tip-selector__input"
              placeholder="введите сумму"
              @blur="confirmCustomAmount"
            />
            <span class="tip-selector__currency">₽</span>
          </div>
        </div>
      </div>

      <div class="user-info">
        <div class="card-order_info">для кого</div>
        <div class="user-info__card">
          <div class="user-info__header">
            <div class="user-info__text">
              <p class="user-info__collecting">{{ collection.title }}</p>
              <div class="user-info__goal">
                {{ collection.description }}
              </div>
            </div>
            <div class="user-info__avatar">
              <img src="@/assets/img/avatars.jpg" alt="User avatar" />
            </div>
          </div>

          <div class="user-info__progress">
            <div class="user-info__progress-text">
              Собрано {{ collection.collected.toLocaleString() }} из
              {{ collection.goal.toLocaleString() }} ₽
            </div>
            <div class="user-info__progress-bar">
              <div
                class="user-info__progress-fill"
                :style="{
                  width: `${(collection.collected / collection.goal) * 100}%`,
                }"
              ></div>
            </div>
          </div>
        </div>

        <div class="action-buttons">
          <button
            class="action-button action-button--primary"
            @click="makeDonation"
            :disabled="!donationAmount"
          >
            Оплатить
          </button>
          <button class="action-button action-button--secondary">
            Другие способы оплаты
          </button>
        </div>

        <div class="service-info">
          сервис представляет <span class="service-info__brand">дайчаевые</span>
        </div>
      </div>
    </div>
    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>
    <AlertsLogin
      v-if="showNotification"
      :message="notificationMessage"
      :duration="3000"
      @close="showNotification = false"
    />

    <div
      v-if="showPaymentPopup"
      class="payment-overlay"
      @click="closePaymentPopup"
    >
      <div class="payment-popup-content">
        <p>
          Подтвердите оплату на сумму {{ donationAmount.toLocaleString() }} ₽
        </p>
        <button class="confirm-payment-button" @click="confirmPayment">
          Подтвердить
        </button>
        <button class="cancel-payment-button" @click="showPaymentPopup = false">
          Отмена
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
.container {
  padding: 0 16px;
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

.card-order_info {
  font-family: "Inter";
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  line-height: 20px;
  letter-spacing: -0.24px;
}

.total-primary {
  padding-top: 12px;
  position: relative;
  display: inline-block;
  background: linear-gradient(180deg, #f80 0%, #ef5000 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.total-primary::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 1px;
  border-bottom: 1px dotted #f80;
}

.tip-selector {
  padding-top: 32px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  border-radius: 12px;
}

.tip-selector__button {
  background-color: #303030;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 12px;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.tip-selector__button:hover {
  background-color: #444;
}

.tip-selector__button--active {
  background-color: #f80;
}

.tip-selector__button--no-hover:hover {
  background-color: #f80 !important;
}

.tip-selector__button--percentage {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
}

.tip-selector__percentage {
  font-family: "Inter";
  font-weight: 500;
  font-size: 28px;
  line-height: 100%;
  letter-spacing: -0.01em;
  margin-bottom: 12px;
  color: #fff;
}

.percentage-gray {
  color: rgba(255, 255, 255, 0.4);
}

.tip-selector__amount {
  border-radius: 6px;
  padding: 2px 4px;
  font-family: "Inter";
  font-style: italic;
  font-weight: 500;
  font-size: 18px;
  line-height: 120%;
  letter-spacing: -0.01em;
  color: #fff;
  background: #000;
}

.tip-selector__button--custom {
  font-family: "Inter";
  grid-column: 1 / -1;
  font-weight: 500;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  color: #fff;
  width: 100%;
  height: 48px;
}

.tip-selector__custom-wrapper {
  grid-column: 1 / -1;
  width: 100%;
}

.tip-selector__input-wrapper {
  position: relative;
  width: 100%;
  height: 48px;
  display: block;
}

.tip-selector__input {
  width: 100%;
  height: 100%;
  padding: 12px 0px;
  text-align: center;
  background-color: #303030;
  border: none;
  border-radius: 10px;
  color: #fff;
  font-family: "Inter";
  font-size: 16px;
  font-weight: 500;
  outline: none;
  transition: background-color 0.2s ease;
  box-sizing: border-box;
}

.tip-selector__input:hover {
  background-color: #444;
}

.tip-selector__input:focus {
  background-color: #444;
  box-shadow: 0 0 0 2px #f80;
}

.tip-selector__currency {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.3);
  font-family: "Inter";
  pointer-events: none;
}

.tip-selector__button--custom .tip-selector__amount {
  display: inline-block;
  background: #000;
  padding: 2px 4px;
  border-radius: 6px;
  font-style: italic;
}

.user-info {
  padding-top: 32px;
}

.user-info__card {
  margin-top: 8px;
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

.user-info__emoji {
  font-size: 20px;
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
  color: #000;
  background: linear-gradient(0deg, #f2eaea 0%, #4f4d4d 100%);
  border-radius: 12px;
  padding: 16px;
}

.service-info {
  margin-top: 32px;
  text-align: center;
  font-family: "Inter";
  font-weight: 500;
  font-size: 14px;
  line-height: 114%;
  letter-spacing: -0.01em;
  text-align: center;
  color: #696d69;
}

.service-info__brand {
  color: #ffffff;
}

.error-message {
  color: #ff4444;
  text-align: center;
  margin-top: 20px;
}

.payment-overlay {
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

.payment-popup-content {
  background: #303030;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  position: relative;
  width: 300px;
  animation: fadeIn 0.3s ease-in-out;
}

.payment-popup-content p {
  font-family: "Inter";
  font-weight: 600;
  font-size: 18px;
  color: #fff;
  margin-bottom: 20px;
}

.confirm-payment-button,
.cancel-payment-button {
  width: 100%;
  font-family: "Inter";
  font-weight: 600;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  text-align: center;
  border: none;
  cursor: pointer;
  padding: 10px;
  border-radius: 12px;
  margin-bottom: 10px;
}

.confirm-payment-button {
  color: #ffffff;
  background: linear-gradient(180deg, #f80 0%, #ef5000 100%);
}

.cancel-payment-button {
  color: #000;
  background: linear-gradient(0deg, #f2eaea 0%, #4f4d4d 100%);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
