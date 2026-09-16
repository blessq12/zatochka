<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import { documentService } from "../../services/DocumentService.js";

export default {
    name: "DocumentsPage",
    components: { ManagerLayout },
    data() {
        return { items: [], selected: null, loading: false, error: null, saved: false };
    },
    async mounted() {
        const data = await documentService.list();
        this.items = data.items || [];
        if (this.items.length) {
            this.select(this.items[0]);
        }
    },
    methods: {
        select(item) {
            this.selected = { ...item };
            this.saved = false;
            this.error = null;
        },
        async save() {
            this.loading = true;
            this.error = null;
            this.saved = false;
            try {
                const updated = await documentService.update(this.selected.slug, {
                    title: this.selected.title,
                    body_html: this.selected.body_html,
                });
                this.selected = updated;
                const idx = this.items.findIndex((i) => i.slug === updated.slug);
                if (idx >= 0) this.items[idx] = updated;
                this.saved = true;
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка сохранения";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Документы</template>
        <div class="grid lg:grid-cols-[240px_1fr] gap-6">
            <div class="bg-white border border-slate-200 p-3 space-y-1">
                <button
                    v-for="item in items"
                    :key="item.slug"
                    type="button"
                    class="w-full text-left px-3 py-2 text-sm"
                    :class="selected?.slug === item.slug ? 'bg-pink-50 text-pink-600 font-jost-bold' : 'hover:bg-slate-50'"
                    @click="select(item)"
                >
                    {{ item.title || item.slug }}
                </button>
            </div>
            <div v-if="selected" class="bg-white border border-slate-200 p-6 space-y-4">
                <div v-if="error" class="bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
                <div v-if="saved" class="bg-green-50 text-green-700 px-4 py-3 text-sm">Сохранено</div>
                <input v-model="selected.title" class="w-full border border-slate-300 px-3 py-2 font-jost-bold" />
                <textarea v-model="selected.body_html" rows="16" class="w-full border border-slate-300 px-3 py-2 font-mono text-sm" />
                <button type="button" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading" @click="save">
                    Сохранить
                </button>
            </div>
        </div>
    </ManagerLayout>
</template>
