<script>
import { actorService } from "../../services/ActorService.js";

export default {
    name: "UserFormPage",
    data() {
        return {
            types: actorService.types,
            loading: false,
            saving: false,
            error: null,
            form: {
                type: this.$route.query.type || this.$route.params.type || "clients",
                email: "",
                password: "",
                name: "",
                phone: "",
                birthday: "",
                delivery_address: "",
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        title() {
            return this.isEdit ? "Редактирование пользователя" : "Новый пользователь";
        },
    },
    async mounted() {
        if (this.isEdit) {
            await this.load();
        }
    },
    methods: {
        typeTitle(type) {
            return actorService.typeLabel(type);
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const type = this.$route.params.type;
                const item = await actorService.get(type, this.$route.params.id);
                this.form = {
                    type,
                    email: item.email || "",
                    password: "",
                    name: item.name || "",
                    phone: item.phone || "",
                    birthday: item.birthday || "",
                    delivery_address: item.delivery_address || "",
                };
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить";
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            this.saving = true;
            this.error = null;
            try {
                if (this.isEdit) {
                    await actorService.update(this.form.type, this.$route.params.id, {
                        name: this.form.name || null,
                        phone: this.form.phone || null,
                        birthday: this.form.birthday || null,
                        delivery_address: this.form.delivery_address || null,
                    });
                } else {
                    if (!this.form.email || !this.form.password || !this.form.name) {
                        this.error = "Укажите имя, email и пароль";
                        return;
                    }
                    await actorService.create(this.form.type, {
                        email: this.form.email,
                        password: this.form.password,
                        name: this.form.name,
                        phone: this.form.phone || null,
                        birthday: this.form.birthday || null,
                        delivery_address: this.form.delivery_address || null,
                    });
                }
                this.$router.push({
                    name: "manager.users",
                    query: { type: this.form.type },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    (e.response?.data?.errors
                        ? Object.values(e.response.data.errors).flat().join(" ")
                        : "Ошибка сохранения");
            } finally {
                this.saving = false;
            }
        },
        cancel() {
            this.$router.push({
                name: "manager.users",
                query: { type: this.form.type },
            });
        },
    },
};
</script>

<template>
    <div class="mx-auto max-w-xl space-y-6">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ title }}</h1>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <form v-if="!loading" class="space-y-4" @submit.prevent="submit">
            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Тип</span>
                <select
                    v-model="form.type"
                    class="w-full border border-slate-300 px-3 py-2"
                    :disabled="isEdit"
                >
                    <option v-for="t in types" :key="t" :value="t">
                        {{ typeTitle(t) }}
                    </option>
                </select>
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Имя</span>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label v-if="!isEdit" class="block space-y-1">
                <span class="text-sm text-slate-600">Email</span>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label v-else class="block space-y-1">
                <span class="text-sm text-slate-600">Email</span>
                <input
                    :value="form.email"
                    type="email"
                    disabled
                    class="w-full border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500"
                />
            </label>

            <label v-if="!isEdit" class="block space-y-1">
                <span class="text-sm text-slate-600">Пароль</span>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Телефон</span>
                <input
                    v-model="form.phone"
                    type="tel"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">День рождения</span>
                <input
                    v-model="form.birthday"
                    type="date"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Адрес доставки</span>
                <input
                    v-model="form.delivery_address"
                    type="text"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <div class="flex gap-3 pt-2">
                <button
                    type="submit"
                    class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                    :disabled="saving"
                >
                    {{ saving ? "Сохранение…" : "Сохранить" }}
                </button>
                <button
                    type="button"
                    class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                    @click="cancel"
                >
                    Отмена
                </button>
            </div>
        </form>
    </div>
</template>
