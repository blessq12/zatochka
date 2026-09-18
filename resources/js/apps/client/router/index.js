import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/authStore.js";

const routes = [
    {
        path: "/login",
        name: "client.login",
        component: () => import("../components/LoginForm.vue"),
        meta: { guest: true },
    },
    {
        path: "/register",
        name: "client.register",
        component: () => import("../components/RegisterForm.vue"),
        meta: { guest: true },
    },
    {
        path: "/",
        component: () => import("../components/Layout/ClientShell.vue"),
        meta: { auth: true },
        children: [
            {
                path: "",
                redirect: { name: "client.orders" },
            },
            {
                path: "orders",
                name: "client.orders",
                component: () => import("../pages/Orders/OrderListPage.vue"),
                meta: { title: "Заказы" },
            },
            {
                path: "orders/create",
                name: "client.orders.create",
                component: () => import("../pages/Orders/OrderCreatePage.vue"),
                meta: { title: "Новый заказ" },
            },
            {
                path: "orders/:id",
                name: "client.orders.show",
                component: () => import("../pages/Orders/OrderShowPage.vue"),
                meta: { title: "Заказ" },
            },
            {
                path: "equipment",
                name: "client.equipment",
                component: () =>
                    import("../pages/Equipment/EquipmentListPage.vue"),
                meta: { title: "Оборудование" },
            },
            {
                path: "equipment/:id",
                name: "client.equipment.show",
                component: () =>
                    import("../pages/Equipment/EquipmentShowPage.vue"),
                meta: { title: "Оборудование" },
            },
            {
                path: "profile",
                name: "client.profile",
                component: () => import("../pages/Profile/ProfilePage.vue"),
                meta: { title: "Профиль" },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: { name: "client.orders" },
    },
];

const router = createRouter({
    history: createWebHistory("/client"),
    routes,
});

router.beforeEach((to) => {
    const store = useAuthStore();

    if (to.meta.auth && !store.isAuthenticated) {
        return { name: "client.login", query: { redirect: to.fullPath } };
    }

    if (to.matched.some((r) => r.meta.auth) && !store.isAuthenticated) {
        return { name: "client.login", query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && store.isAuthenticated) {
        return { name: "client.orders" };
    }

    return true;
});

export default router;
