import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";

import HomePage from "@/views/HomePage.vue";
import ProtectedPage from "@/views/ProtectedPage.vue";

const routes: Array<RouteRecordRaw> = [
  { path: "/", name: "Home", component: HomePage },
  { path: "/protected", name: "Protected", component: ProtectedPage },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
