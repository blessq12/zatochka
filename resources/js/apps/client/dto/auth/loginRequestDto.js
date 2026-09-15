// DTO: Login request payload
// Usage: import createLoginRequestDto from "./loginRequestDto";
//        const payload = createLoginRequestDto({ phone, password });

export default function createLoginRequestDto({
    phone = "",
    password = "",
} = {}) {
    return {
        phone,
        password,
    };
}
