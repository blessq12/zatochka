/**
 * @param {{ price: string|number, prefix?: 'from' | 'to' | null }} item
 */
export function formatPriceItem(item) {
    const price = `${item.price}₽`;

    if (item.prefix === "from") {
        return `от ${price}`;
    }

    if (item.prefix === "to") {
        return `до ${price}`;
    }

    return price;
}
