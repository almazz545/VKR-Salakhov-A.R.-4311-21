import { createRouter, createWebHistory } from "vue-router";
import DonationPage from "../views/DonationPage.vue";
import RegistrationPage from "../views/RegistrationPage.vue";
import LoginPage from "../views/LoginPage.vue";
import DashboardPage from "../views/DashboardPage.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      redirect: () => {
        const isAuthenticated = !!localStorage.getItem("token");
        return isAuthenticated ? "/dashboard" : "/login";
      },
    },
    {
      path: "/auth",
      component: RegistrationPage,
    },
    {
      path: "/login",
      component: LoginPage,
    },
    {
      path: "/dashboard",
      component: DashboardPage,
      meta: { requiresAuth: true },
    },
    {
      path: "/collection/:id",
      component: DonationPage,
      props: true,
    },
  ],
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem("token");
  if (to.meta.requiresAuth && !isAuthenticated) {
    next("/login");
  } else {
    next();
  }
});

export default router;
