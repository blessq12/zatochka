export const navigationItems = [
    {
        name: "client.profile",
        label: "Профиль",
        icon: "users",
        to: { name: "client.profile" },
    },
    {
        name: "client.orders",
        label: "Заказы",
        icon: "orders",
        to: { name: "client.orders" },
    },
    {
        name: "client.equipment",
        label: "Оборудование",
        icon: "equipment",
        to: { name: "client.equipment" },
    },
    {
        name: "client.orders.create",
        label: "Новый заказ",
        shortLabel: "Новый",
        icon: "plus",
        to: { name: "client.orders.create" },
    },
];

export const bottomNavItems = navigationItems;

/**
 * Активный пункт меню клиента (create не подсвечивает «Заказы»).
 * @param {{ name?: string }} item
 * @param {{ name?: string|symbol|null }} route
 */
export function isClientNavActive(item, route) {
    const routeName = String(route.name || "");
    const itemName = String(item.name || "");

    if (itemName === "client.orders") {
        return (
            routeName === "client.orders" || routeName === "client.orders.show"
        );
    }

    if (itemName === "client.equipment") {
        return (
            routeName === "client.equipment" ||
            routeName === "client.equipment.show"
        );
    }

    return routeName === itemName;
}
