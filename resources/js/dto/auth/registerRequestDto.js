// DTO: Register request payload
// Usage: import createRegisterRequestDto from "./registerRequestDto";
//        const payload = createRegisterRequestDto({ fullName, email, phone, password, passwordConfirmation });

export default function createRegisterRequestDto({
    fullName = "",
    email = "",
    phone = "",
    password = "",
    passwordConfirmation = "",
} = {}) {
    return {
        full_name: fullName,
        email,
        phone,
        password,
        password_confirmation: passwordConfirmation,
    };
}
