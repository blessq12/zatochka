/**
 * Shared active-route check for shell navigation items.
 * @param {{ name?: string, to?: string|object, match?: string }} item
 * @param {{ name?: string|symbol|null, path: string }} route
 */
export function isNavItemActive(item, route) {
    const routeName = String(route.name || "");

    if (item.match) {
        if (item.match === "dashboard") {
            return routeName.endsWith(".dashboard");
        }
        if (item.match === "orders") {
            return (
                routeName.includes(".orders")
                || routeName === "pos.job"
                || routeName.endsWith(".job")
            );
        }
        return routeName.includes(`.${item.match}`);
    }

    if (item.name) {
        return (
            routeName === item.name
            || routeName.startsWith(`${item.name}.`)
        );
    }

    if (typeof item.to === "string") {
        if (item.to === "/") {
            return route.path === "/" || route.path === "";
        }
        return (
            route.path === item.to
            || route.path.startsWith(`${item.to}/`)
        );
    }

    if (item.to?.name) {
        const base = String(item.to.name);
        return routeName === base || routeName.startsWith(`${base}.`);
    }

    return false;
}
