export const navigationItems = [
    {
        name: "client.orders",
        label: "Заказы",
        icon: "orders",
        to: { name: "client.orders" },
        match: "orders",
    },
    {
        name: "client.orders.create",
        label: "Новый",
        icon: "dashboard",
        to: { name: "client.orders.create" },
    },
    {
        name: "client.equipment",
        label: "Оборудование",
        icon: "equipment",
        to: { name: "client.equipment" },
        match: "equipment",
    },
    {
        name: "client.profile",
        label: "Профиль",
        icon: "users",
        to: { name: "client.profile" },
    },
];

export const bottomNavItems = navigationItems;
