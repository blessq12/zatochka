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
                path: "orders",
                name: "pos.orders",
                component: () => import("../pages/Pos/OrdersPage.vue"),
            },
            {
                path: "orders/jobs/:id",
                name: "pos.job",
                component: () => import("../pages/Pos/JobPage.vue"),
            },
            {
                path: "equipment",
                name: "pos.equipment",
                component: () => import("../pages/Pos/EquipmentListPage.vue"),
            },
            {
                path: "warehouse",
                name: "pos.warehouse",
                component: () => import("../pages/Pos/WarehouseCatalogPage.vue"),
            },
            {
                path: "",
                redirect: { name: "pos.orders" },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "pos.orders" },
    },
];

const router = createRouter({
    history: createWebHistory("/master"),
    routes,
});

export default router;
