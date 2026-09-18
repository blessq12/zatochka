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
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Пользователи</h1>
            <button type="button" class="app-btn-primary w-full sm:w-auto" @click="goCreate">
                Создать
            </button>
        </div>

        <div class="app-tabs">
            <button
                v-for="tab in types"
                :key="tab"
                type="button"
                class="app-tab"
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

        <template v-else>
            <div class="app-card-list">
                <p v-if="items.length === 0" class="app-card text-slate-500">
                    Нет {{ typeLabel.toLowerCase() }}
                </p>
                <div v-for="item in items" :key="item.id" class="app-card">
                    <div class="flex items-start justify-between gap-2">
                        <div class="font-jost-medium text-dark-blue-500">
                            {{ item.name || "—" }}
                        </div>
                        <span class="text-xs text-slate-400">#{{ item.id }}</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ item.email || "без email" }}</p>
                    <p class="text-sm text-slate-600">
                        Тел: {{ item.phone || "—" }}
                    </p>
                    <p class="text-sm text-slate-500">
                        ДР: {{ item.birthday || "—" }}
                    </p>
                    <p class="text-xs text-slate-500">
                        Адрес: {{ item.delivery_address || "—" }}
                    </p>
                    <div class="app-actions pt-1">
                        <button
                            type="button"
                            class="app-btn-secondary"
                            @click="goEdit(item)"
                        >
                            Изменить
                        </button>
                        <button
                            type="button"
                            class="app-btn-ghost text-red-600"
                            @click="remove(item)"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>

            <div class="app-table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-jost-medium">#</th>
                            <th class="px-4 py-3 font-jost-medium">Имя</th>
                            <th class="px-4 py-3 font-jost-medium">Email</th>
                            <th class="px-4 py-3 font-jost-medium">Телефон</th>
                            <th class="px-4 py-3 font-jost-medium">День рождения</th>
                            <th class="px-4 py-3 font-jost-medium">Адрес</th>
                            <th class="px-4 py-3 font-jost-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length === 0">
                            <td colspan="7" class="px-4 py-6 text-slate-500">
                                Нет {{ typeLabel.toLowerCase() }}
                            </td>
                        </tr>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-b border-slate-100"
                        >
                            <td class="px-4 py-3">{{ item.id }}</td>
                            <td class="px-4 py-3">{{ item.name || "—" }}</td>
                            <td class="px-4 py-3">{{ item.email || "—" }}</td>
                            <td class="px-4 py-3">{{ item.phone || "—" }}</td>
                            <td class="px-4 py-3">{{ item.birthday || "—" }}</td>
                            <td class="px-4 py-3">
                                {{ item.delivery_address || "—" }}
                            </td>
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
        </template>
    </div>
</template>
