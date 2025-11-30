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
        path: "/help",
        name: "help",
        component: () => import("../components/HelpPage.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
