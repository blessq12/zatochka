// DTO: Login request payload for Identity API

export default function createLoginRequestDto({
    email = "",
    password = "",
    expectedActorType = "clients",
} = {}) {
    return {
        email,
        password,
        expected_actor_type: expectedActorType,
    };
}
