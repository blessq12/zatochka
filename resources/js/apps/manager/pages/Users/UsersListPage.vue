<script>
import { actorService } from "../../services/ActorService.js";

export default {
    name: "UsersListPage",
    data() {
        return {
            type: this.$route.query.type || "clients",
            items: [],
            loading: false,
            error: null,
            types: actorService.types,
        };
    },
    computed: {
        typeLabel() {
            return actorService.typeLabel(this.type);
        },
    },
    watch: {
        "$route.query.type"(value) {
            this.type = value || "clients";
            this.load();
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        typeTitle(type) {
            return actorService.typeLabel(type);
        },
        selectType(type) {
            this.$router.replace({ name: "manager.users", query: { type } });
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await actorService.list(this.type);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        goCreate() {
            this.$router.push({
                name: "manager.users.create",
                query: { type: this.type },
            });
        },
        goEdit(item) {
            this.$router.push({
                name: "manager.users.edit",
                params: { type: this.type, id: String(item.id) },
            });
        },
        async remove(item) {
            if (!confirm(`Удалить «${item.name || item.email}»?`)) {
                return;
            }
            try {
                await actorService.remove(this.type, item.id);
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось удалить";
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">
                Пользователи
            </h1>
            <button
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600"
                @click="goCreate"
            >
                Создать
            </button>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="tab in types"
                :key="tab"
                type="button"
                class="border px-3 py-1.5 text-sm font-jost-medium"
                :class="
                    type === tab
                        ? 'border-pink-500 bg-pink-50 text-pink-600'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                "
                @click="selectType(tab)"
            >
                {{ typeTitle(tab) }}
            </button>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <div v-else class="overflow-x-auto border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-jost-medium">Имя</th>
                        <th class="px-4 py-3 font-jost-medium">Email</th>
                        <th class="px-4 py-3 font-jost-medium">Телефон</th>
                        <th class="px-4 py-3 font-jost-medium">День рождения</th>
                        <th class="px-4 py-3 font-jost-medium" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0">
                        <td colspan="5" class="px-4 py-6 text-slate-500">
                            Нет {{ typeLabel.toLowerCase() }}
                        </td>
                    </tr>
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="border-b border-slate-100"
                    >
                        <td class="px-4 py-3">{{ item.name || "—" }}</td>
                        <td class="px-4 py-3">{{ item.email || "—" }}</td>
                        <td class="px-4 py-3">{{ item.phone || "—" }}</td>
                        <td class="px-4 py-3">{{ item.birthday || "—" }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <button
                                type="button"
                                class="mr-3 text-pink-500 hover:underline"
                                @click="goEdit(item)"
                            >
                                Изменить
                            </button>
                            <button
                                type="button"
                                class="text-slate-500 hover:text-red-600 hover:underline"
                                @click="remove(item)"
                            >
                                Удалить
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
