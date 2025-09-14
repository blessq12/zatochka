<script>
import { object, string } from "yup";
export default {
    name: "RegisterForm",
    data() {
        return {
            form: object({
                name: string().required(),
                email: string().email().required(),
                password: string().required(),
                telegram: string().required(),
            }),
        };
    },
    methods: {
        handleSubmit() {
            console.log(this.form);
        },
    },
};
</script>

<template>
    <div>
        <h1>Register</h1>
        <form
            @submit.prevent="handleSubmit"
            class="flex flex-col gap-2 max-w-md"
        >
            <input
                type="text"
                v-model="form.name"
                class="border border-gray-300 rounded-md p-2"
            />
            <input
                type="email"
                v-model="form.email"
                class="border border-gray-300 rounded-md p-2"
            />
            <input
                type="password"
                v-model="form.password"
                class="border border-gray-300 rounded-md p-2"
            />
            <input
                type="text"
                v-model="form.telegram"
                class="border border-gray-300 rounded-md p-2"
            />
            <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded"
            >
                Register
            </button>
        </form>
    </div>
</template>

<style scoped></style>
