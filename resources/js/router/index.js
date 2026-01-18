import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "home",
        component: () => import("../pages/HomePage.vue"),
    },
    {
        path: "/sharpening",
        name: "sharpening",
        component: () => import("../pages/SharpeningPage.vue"),
    },
    {
        path: "/repair",
        name: "repair",
        component: () => import("../pages/RepairPage.vue"),
    },
    {
        path: "/delivery",
        name: "delivery",
        component: () => import("../pages/DeliveryPage.vue"),
    },
    {
        path: "/contacts",
        name: "contacts",
        component: () => import("../pages/ContactsPage.vue"),
    },
    {
        path: "/client/dashboard",
        name: "client.dashboard",
        component: () => import("../components/ClientApp.vue"),
    },
    {
        path: "/work-schedule",
        name: "workSchedule",
        component: () => import("../pages/WorkSchedulePage.vue"),
    },
    {
        path: "/prices",
        name: "prices",
        component: () => import("../pages/PricePage.vue"),
    },
    {
        path: "/privacy-policy",
        name: "privacy-policy",
        component: () => import("../pages/PrivacyPolicyPage.vue"),
    },
    {
        path: "/terms-of-service",
        name: "terms-of-service",
        component: () => import("../pages/TermsOfServicePage.vue"),
    },
    {
        path: "/pos",
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
                component: () => import("../pages/Pos/OrdersNewPage.vue"),
            },
            {
                path: "orders/active",
                name: "pos.orders.active",
                component: () => import("../pages/Pos/OrdersActivePage.vue"),
            },
            {
                path: "orders/waiting-parts",
                name: "pos.orders.waiting-parts",
                component: () => import("../pages/Pos/OrdersWaitingPartsPage.vue"),
            },
            {
                path: "orders/in-work/:id",
                name: "pos.orders.in-work",
                component: () => import("../pages/Pos/OrderInWorkPage.vue"),
            },
            {
                path: "orders/completed",
                name: "pos.orders.completed",
                component: () => import("../pages/Pos/OrdersCompletedPage.vue"),
            },
            {
                path: "warehouse",
                name: "pos.warehouse.index",
                component: () => import("../pages/Pos/WarehousePage.vue"),
            },
            {
                path: "settings/profile",
                name: "pos.settings.profile",
                component: () => import("../pages/Pos/SettingsProfilePage.vue"),
            },
            {
                path: "settings/telegram",
                name: "pos.settings.telegram",
                component: () => import("../pages/Pos/SettingsTelegramPage.vue"),
            },
            {
                path: "",
                redirect: { name: "pos.dashboard" },
            },
        ],
    },
    {
        path: "/403",
        name: "Forbidden",
        component: () => import("../pages/ForbiddenPage.vue"),
    },
    {
        path: "/:pathMatch(.*)*",
        name: "PageNotFound",
        component: () => import("../pages/PageNotFound.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        return { top: 0, behavior: "smooth" };
    },
});

export default router;
