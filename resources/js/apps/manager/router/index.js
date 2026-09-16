import { createRouter, createWebHistory } from "vue-router";
import { useManagerStore } from "../stores/managerStore.js";

const routes = [
    {
        path: "/login",
        name: "manager.login",
        component: () => import("../components/LoginForm.vue"),
        meta: { guest: true },
    },
    {
        path: "/",
        name: "manager.dashboard",
        component: () => import("../pages/DashboardPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/users",
        name: "manager.users",
        component: () => import("../pages/Users/UsersListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/users/create",
        name: "manager.users.create",
        component: () => import("../pages/Users/UserFormPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/users/:id/edit",
        name: "manager.users.edit",
        component: () => import("../pages/Users/UserFormPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/clients",
        name: "manager.clients",
        component: () => import("../pages/Clients/ClientsListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/clients/create",
        name: "manager.clients.create",
        component: () => import("../pages/Clients/ClientFormPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/clients/:id",
        name: "manager.clients.view",
        component: () => import("../pages/Clients/ClientViewPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/clients/:id/edit",
        name: "manager.clients.edit",
        component: () => import("../pages/Clients/ClientFormPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/orders",
        name: "manager.orders",
        component: () => import("../pages/Orders/OrdersListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/orders/create",
        name: "manager.orders.create",
        component: () => import("../pages/Orders/OrderCreatePage.vue"),
        meta: { auth: true },
    },
    {
        path: "/orders/:id",
        name: "manager.orders.view",
        component: () => import("../pages/Orders/OrderViewPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/equipment",
        name: "manager.equipment",
        component: () => import("../pages/Equipment/EquipmentListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/equipment/create",
        name: "manager.equipment.create",
        component: () => import("../pages/Equipment/EquipmentCreatePage.vue"),
        meta: { auth: true },
    },
    {
        path: "/equipment/:id",
        name: "manager.equipment.view",
        component: () => import("../pages/Equipment/EquipmentViewPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/stock-items",
        name: "manager.stock",
        component: () => import("../pages/Stock/StockListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/stock-items/:id",
        name: "manager.stock.view",
        component: () => import("../pages/Stock/StockViewPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/cash-desk",
        name: "manager.cash-desk",
        component: () => import("../pages/Finance/CashDeskPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/cash-operations",
        name: "manager.cash-operations",
        component: () => import("../pages/Finance/CashOperationsListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/reviews",
        name: "manager.reviews",
        component: () => import("../pages/Reviews/ReviewsListPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/reviews/:id",
        name: "manager.reviews.view",
        component: () => import("../pages/Reviews/ReviewViewPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/site",
        name: "manager.site",
        component: () => import("../pages/Site/SiteContentPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/documents",
        name: "manager.documents",
        component: () => import("../pages/Documents/DocumentsPage.vue"),
        meta: { auth: true },
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "manager.dashboard" },
    },
];

const router = createRouter({
    history: createWebHistory("/manager"),
    routes,
});

router.beforeEach((to) => {
    const store = useManagerStore();
    if (!store.token) {
        store.restoreSession();
    }

    if (to.meta.auth && !store.isAuthenticated) {
        return { name: "manager.login", query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && store.isAuthenticated) {
        return { name: "manager.dashboard" };
    }

    return true;
});

export default router;
