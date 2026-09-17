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
        component: () => import("../components/Layout/ManagerShell.vue"),
        meta: { auth: true },
        children: [
            {
                path: "",
                name: "manager.dashboard",
                component: () => import("../pages/DashboardPage.vue"),
                meta: { title: "Дашборд" },
            },
            {
                path: "users",
                name: "manager.users",
                component: () => import("../pages/Users/UsersListPage.vue"),
                meta: { title: "Пользователи" },
            },
            {
                path: "users/create",
                name: "manager.users.create",
                component: () => import("../pages/Users/UserFormPage.vue"),
                meta: { title: "Новый пользователь" },
            },
            {
                path: "users/:type/:id/edit",
                name: "manager.users.edit",
                component: () => import("../pages/Users/UserFormPage.vue"),
                meta: { title: "Редактирование пользователя" },
            },
            {
                path: "site",
                name: "manager.site",
                component: () => import("../pages/Site/SiteContentPage.vue"),
                meta: { title: "Контент сайта" },
            },
            {
                path: "equipment",
                name: "manager.equipment",
                component: () =>
                    import("../pages/Equipment/EquipmentListPage.vue"),
                meta: { title: "Оборудование" },
            },
            {
                path: "equipment/create",
                name: "manager.equipment.create",
                component: () =>
                    import("../pages/Equipment/EquipmentFormPage.vue"),
                meta: { title: "Новое оборудование" },
            },
            {
                path: "equipment/:id/edit",
                name: "manager.equipment.edit",
                component: () =>
                    import("../pages/Equipment/EquipmentFormPage.vue"),
                meta: { title: "Редактирование оборудования" },
            },
            {
                path: "orders",
                name: "manager.orders",
                component: () => import("../pages/Orders/OrderListPage.vue"),
                meta: { title: "Заказы" },
            },
            {
                path: "orders/create",
                name: "manager.orders.create",
                component: () => import("../pages/Orders/OrderFormPage.vue"),
                meta: { title: "Новый заказ" },
            },
            {
                path: "orders/:id",
                name: "manager.orders.show",
                component: () => import("../pages/Orders/OrderShowPage.vue"),
                meta: { title: "Заказ" },
            },
        ],
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

    if (to.matched.some((r) => r.meta.auth) && !store.isAuthenticated) {
        return { name: "manager.login", query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && store.isAuthenticated) {
        return { name: "manager.dashboard" };
    }

    return true;
});

export default router;
