<script setup>
import { auth } from "@/api/auth";
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const email = ref("");
const password = ref("");
const showPassword = ref(false);
const errorMessage = ref("");

const handleSubmit = async () => {
  try {
    const response = await auth.login(email.value, password.value);
    if (response.success) {
      router.push("/dashboard");
    }
  } catch (error) {
    errorMessage.value = error.message || "Ошибка при входе";
  }
};

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <main>
    <div class="container">
      <div class="header-wrapper">
        <div class="card-title">вход в аккаунт</div>
      </div>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
        <div class="input-container">
          <div class="input-wrapper">
            <input
              v-model="email"
              type="text"
              class="tip-selector__input"
              placeholder="ваш e-mail"
            />
          </div>

          <div class="input-wrapper password-wrapper">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="tip-selector__input"
              placeholder="пароль"
            />
            <button
              type="button"
              class="password-toggle"
              @click="togglePasswordVisibility"
            >
              <img src="/src/assets/img/icons/eye.svg" alt="" />
            </button>
          </div>
        </div>

        <div class="action-buttons">
          <button type="submit" class="action-button action-button--primary">
            войти
          </button>
          <button
            type="button"
            class="action-button action-button--secondary"
            @click="$router.push('/auth')"
          >
            у меня нет аккаунта
          </button>
        </div>
      </form>
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

.login-form {
  margin-top: 140px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-container {
  background-color: #303030;
  border-radius: 12px;
}

.input-wrapper {
  position: relative;
  width: 100%;
  height: 48px;
}

.password-wrapper {
  position: relative;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.error-message {
  font-family: "Inter";
  font-weight: 500;
  font-size: 14px;
  color: #ff4444;
  margin-bottom: 8px;
  text-align: center;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  padding: 0;
  font-size: 16px;
  user-select: none;
}

.tip-selector__input {
  width: 100%;
  height: 100%;
  padding: 12px 16px;

  border: none;
  color: #fff;
  font-family: "Inter";
  font-size: 16px;
  font-weight: 500;
  outline: none;
  transition: background-color 0.2s ease;
  box-sizing: border-box;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  margin-top: 24px;
}

.action-button {
  width: 100%;
  font-family: "Inter";
  font-weight: 500;
  font-size: 16px;
  line-height: 125%;
  letter-spacing: -0.01em;
  text-align: center;
  border: none;
  cursor: pointer;
}

.action-button--primary {
  color: #ffffff;
  border-radius: 12px;
  padding: 16px;
  background-color: #1a1a1a;
}

.action-button--secondary {
  color: #ffffff;
  border-radius: 12px;
  padding: 16px;
}
</style>
