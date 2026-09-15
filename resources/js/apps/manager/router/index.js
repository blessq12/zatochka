import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "manager.home",
        component: () => import("../pages/ShellPage.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "manager.home" },
    },
];

const router = createRouter({
    history: createWebHistory("/manager"),
    routes,
});

export default router;
