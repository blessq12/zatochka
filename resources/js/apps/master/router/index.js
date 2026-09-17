import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "pos",
        component: () => import("../pages/PosPage.vue"),
        children: [
            {
                path: "dashboard",
                name: "pos.dashboard",
                component: () => import("../pages/Pos/DashboardPage.vue"),
            },
            {
                path: "",
                redirect: { name: "pos.dashboard" },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "pos.dashboard" },
    },
];

const router = createRouter({
    history: createWebHistory("/master"),
    routes,
});

export default router;
