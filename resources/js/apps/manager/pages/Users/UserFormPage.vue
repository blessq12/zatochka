<script>
import { actorService } from "../../services/ActorService.js";
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "UserFormPage",
    data() {
        return {
            types: actorService.types,
            loading: false,
            saving: false,
            error: null,
            activeTab: this.$route.query.tab === "equipment" ? "equipment" : "data",
            equipmentItems: [],
            equipmentLoading: false,
            equipmentError: null,
            equipmentLoaded: false,
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
        isClientEdit() {
            return this.isEdit && this.form.type === "clients";
        },
        title() {
            return this.isEdit ? "Редактирование пользователя" : "Новый пользователь";
        },
        showTabs() {
            return this.isClientEdit;
        },
    },
    watch: {
        activeTab(tab) {
            if (tab === "equipment" && this.isClientEdit && !this.equipmentLoaded) {
                this.loadEquipment();
            }
            if (this.isClientEdit) {
                const query = { ...this.$route.query };
                if (tab === "equipment") {
                    query.tab = "equipment";
                } else {
                    delete query.tab;
                }
                this.$router.replace({ query });
            }
        },
    },
    async mounted() {
        if (this.isEdit) {
            await this.load();
            if (this.isClientEdit && this.activeTab === "equipment") {
                await this.loadEquipment();
            }
        }
    },
    methods: {
        typeTitle(type) {
            return actorService.typeLabel(type);
        },
        selectTab(tab) {
            this.activeTab = tab;
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
        async loadEquipment() {
            this.equipmentLoading = true;
            this.equipmentError = null;
            try {
                this.equipmentItems = await equipmentService.list(
                    this.$route.params.id,
                );
                this.equipmentLoaded = true;
            } catch (e) {
                this.equipmentError =
                    e.response?.data?.message ||
                    "Не удалось загрузить оборудование";
                this.equipmentItems = [];
            } finally {
                this.equipmentLoading = false;
            }
        },
        goCreateEquipment() {
            this.$router.push({
                name: "manager.equipment.create",
                query: { client_id: String(this.$route.params.id) },
            });
        },
        goEditEquipment(item) {
            this.$router.push({
                name: "manager.equipment.edit",
                params: { id: String(item.id) },
            });
        },
        goAllEquipment() {
            this.$router.push({
                name: "manager.equipment",
                query: { client_id: String(this.$route.params.id) },
            });
        },
        async removeEquipment(item) {
            if (!confirm(`Удалить «${item.name}»?`)) {
                return;
            }
            try {
                await equipmentService.remove(item.id);
                await this.loadEquipment();
            } catch (e) {
                this.equipmentError =
                    e.response?.data?.message || "Не удалось удалить";
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
    <div
        class="w-full space-y-4 sm:space-y-6"
    >
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ title }}</h1>

        <div v-if="showTabs" class="flex flex-wrap gap-2">
            <button
                type="button"
                class="border px-3 py-1.5 text-sm font-jost-medium"
                :class="
                    activeTab === 'data'
                        ? 'border-pink-500 bg-pink-50 text-pink-600'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                "
                @click="selectTab('data')"
            >
                Данные
            </button>
            <button
                type="button"
                class="border px-3 py-1.5 text-sm font-jost-medium"
                :class="
                    activeTab === 'equipment'
                        ? 'border-pink-500 bg-pink-50 text-pink-600'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                "
                @click="selectTab('equipment')"
            >
                Оборудование
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error && (!showTabs || activeTab === 'data')" class="text-sm text-red-600">
            {{ error }}
        </p>

        <form
            v-if="!loading && (!showTabs || activeTab === 'data')"
            class="space-y-4"
            @submit.prevent="submit"
        >
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

        <section
            v-if="showTabs && activeTab === 'equipment' && !loading"
            class="space-y-4"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-slate-600">
                    Оборудование клиента
                    <span v-if="form.name" class="font-jost-medium text-dark-blue-500">
                        {{ form.name }}
                    </span>
                </p>
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="text-sm text-pink-600 hover:underline"
                        @click="goAllEquipment"
                    >
                        Весь список
                    </button>
                    <button
                        type="button"
                        class="bg-pink-500 px-3 py-1.5 text-sm font-jost-medium text-white hover:bg-pink-600"
                        @click="goCreateEquipment"
                    >
                        Добавить
                    </button>
                </div>
            </div>

            <p v-if="equipmentLoading" class="text-sm text-slate-500">
                Загрузка…
            </p>
            <p v-if="equipmentError" class="text-sm text-red-600">
                {{ equipmentError }}
            </p>

            <div
                v-if="!equipmentLoading"
                class="overflow-x-auto border border-slate-200 bg-white"
            >
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-jost-medium">Название</th>
                            <th class="px-4 py-3 font-jost-medium">Бренд</th>
                            <th class="px-4 py-3 font-jost-medium">Тип</th>
                            <th class="px-4 py-3 font-jost-medium">Модули</th>
                            <th class="px-4 py-3 font-jost-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="equipmentItems.length === 0">
                            <td colspan="5" class="px-4 py-6 text-slate-500">
                                У клиента пока нет оборудования
                            </td>
                        </tr>
                        <tr
                            v-for="item in equipmentItems"
                            :key="item.id"
                            class="border-t border-slate-100"
                        >
                            <td class="px-4 py-3">{{ item.name }}</td>
                            <td class="px-4 py-3">{{ item.brand }}</td>
                            <td class="px-4 py-3">{{ item.type }}</td>
                            <td class="px-4 py-3">
                                {{ item.modules?.length || 0 }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <button
                                    type="button"
                                    class="text-pink-600 hover:underline"
                                    @click="goEditEquipment(item)"
                                >
                                    Изменить
                                </button>
                                <button
                                    type="button"
                                    class="text-red-600 hover:underline"
                                    @click="removeEquipment(item)"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button
                type="button"
                class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                @click="cancel"
            >
                К списку пользователей
            </button>
        </section>
    </div>
</template>
