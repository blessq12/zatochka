// DTO: Register request payload for Identity API (client SPA only)

export default function createRegisterRequestDto({
    email = "",
    password = "",
} = {}) {
    return {
        actor_type: "clients",
        email,
        password,
    };
}
