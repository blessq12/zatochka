export default function createUpdateClientRequestDto(input) {
    const payload = {};

    const fields = [
        "full_name",
        "email",
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
