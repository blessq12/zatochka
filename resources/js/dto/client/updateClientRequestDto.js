export default function createUpdateClientRequestDto(input) {
    const payload = {
        id: input.id,
    };

    const fields = [
        "full_name",
        "email",
        "telegram",
        "birth_date",
        "delivery_address",
    ];

    fields.forEach((key) => {
        if (Object.prototype.hasOwnProperty.call(input, key)) {
            payload[key] = input[key];
        }
    });

    return payload;
}
