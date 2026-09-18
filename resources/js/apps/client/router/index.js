import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        redirect: { name: "client.dashboard" },
    },
    {
        path: "/dashboard",
        name: "client.dashboard",
        component: () => import("../components/ClientApp.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "client.dashboard" },
    },
];

const router = createRouter({
    history: createWebHistory("/client"),
    routes,
});

export default router;
