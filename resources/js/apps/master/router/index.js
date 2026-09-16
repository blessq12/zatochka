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
                path: "orders/new",
                name: "pos.orders.new",
                component: () => import("../pages/Pos/OrdersListPage.vue"),
                meta: { posOrderTab: "new" },
            },
            {
                path: "orders/in-work",
                name: "pos.orders.in-work",
                component: () => import("../pages/Pos/OrdersListPage.vue"),
                meta: { posOrderTab: "in_work" },
            },
            {
                path: "orders/waiting-parts",
                name: "pos.orders.waiting-parts",
                component: () => import("../pages/Pos/OrdersListPage.vue"),
                meta: { posOrderTab: "waiting_parts" },
            },
            {
                path: "orders/ready",
                name: "pos.orders.ready",
                component: () => import("../pages/Pos/OrdersListPage.vue"),
                meta: { posOrderTab: "ready" },
            },
            {
                path: "orders/active",
                redirect: { name: "pos.orders.in-work" },
            },
            {
                path: "orders/completed",
                redirect: { name: "pos.orders.ready" },
            },
            {
                path: "orders/in-work/:id",
                redirect: (to) => ({
                    name: "pos.orders.detail",
                    params: { id: to.params.id },
                }),
            },
            {
                path: "orders/:id",
                name: "pos.orders.detail",
                component: () => import("../pages/Pos/OrderInWorkPage.vue"),
            },
            {
                path: "warehouse",
                name: "pos.warehouse.index",
                component: () => import("../pages/Pos/WarehousePage.vue"),
            },
            {
                path: "equipment/search",
                name: "pos.equipment.search",
                component: () =>
                    import("../pages/Pos/EquipmentSearchPage.vue"),
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
