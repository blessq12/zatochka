export const navigationItems = [
    {
        label: "Дашборд",
        to: { name: "pos.dashboard" },
        match: "dashboard",
    },
    {
        label: "Заказы",
        to: { name: "pos.orders.new" },
        match: "orders",
    },
    {
        label: "Склад",
        to: { name: "pos.warehouse.index" },
        match: "warehouse",
    },
    {
        label: "Оборудование",
        to: { name: "pos.equipment.search" },
        match: "equipment",
    },
];
