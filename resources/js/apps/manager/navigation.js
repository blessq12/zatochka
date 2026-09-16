export const navigationGroups = [
    {
        label: "Клиенты",
        items: [
            { name: "manager.clients", label: "Клиенты", to: "/clients" },
            { name: "manager.reviews", label: "Отзывы", to: "/reviews" },
        ],
    },
    {
        label: "Заказы",
        items: [{ name: "manager.orders", label: "Заказы", to: "/orders" }],
    },
    {
        label: "Оборудование",
        items: [{ name: "manager.equipment", label: "Оборудование", to: "/equipment" }],
    },
    {
        label: "Склад",
        items: [{ name: "manager.stock", label: "Склад", to: "/stock-items" }],
    },
    {
        label: "Финансы",
        items: [
            { name: "manager.cash-desk", label: "Касса", to: "/cash-desk" },
            { name: "manager.cash-operations", label: "Операции", to: "/cash-operations" },
        ],
    },
    {
        label: "Сотрудники",
        items: [{ name: "manager.users", label: "Сотрудники", to: "/users" }],
    },
    {
        label: "Сайт",
        items: [
            { name: "manager.site", label: "Контент", to: "/site" },
            { name: "manager.documents", label: "Документы", to: "/documents" },
        ],
    },
];
