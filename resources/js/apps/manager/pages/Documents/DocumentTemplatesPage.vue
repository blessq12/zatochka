<script>
import { documentService } from "../../services/DocumentService.js";

export default {
    name: "DocumentTemplatesPage",
    data() {
        return {
            loading: false,
            saving: false,
            error: null,
            success: null,
            templates: [],
            variables: [],
            loops: [],
            activeType: "receipt",
            body: "",
            previewOrderId: "",
        };
    },
    computed: {
        activeTemplate() {
            return this.templates.find((row) => row.type === this.activeType) || null;
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const data = await documentService.listTemplates();
                this.templates = data.templates || [];
                this.variables = data.variables || [];
                this.loops = data.loops || [];
                if (!this.templates.some((row) => row.type === this.activeType)) {
                    this.activeType = this.templates[0]?.type || "receipt";
                }
                this.body = this.activeTemplate?.body || "";
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить шаблоны";
            } finally {
                this.loading = false;
            }
        },
        selectType(type) {
            this.activeType = type;
            this.body = this.templates.find((row) => row.type === type)?.body || "";
            this.success = null;
            this.error = null;
        },
        async save() {
            this.saving = true;
            this.error = null;
            this.success = null;
            try {
                const saved = await documentService.updateTemplate(
                    this.activeType,
                    this.body,
                );
                const index = this.templates.findIndex(
                    (row) => row.type === saved.type,
                );
                if (index >= 0) {
                    this.templates.splice(index, 1, saved);
                } else {
                    this.templates.push(saved);
                }
                this.success = "Шаблон сохранён";
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сохранить шаблон";
            } finally {
                this.saving = false;
            }
        },
        async preview() {
            this.error = null;
            const previewWindow = window.open("about:blank", "_blank");
            try {
                const orderId = this.previewOrderId
                    ? Number(this.previewOrderId)
                    : null;
                await documentService.previewTemplate(
                    this.activeType,
                    this.body,
                    orderId,
                    previewWindow,
                );
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    e.message ||
                    "Не удалось открыть предпросмотр";
            }
        },
        insertVariable(key) {
            this.body = `${this.body}{{${key}}}`;
        },
        formatVar(key) {
            return `{{${key}}}`;
        },
        formatLoop(loop) {
            return `{{#each ${loop.key}}} … {{/each}} (${loop.fields.join(", ")})`;
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Документы заказа</h1>
        </div>

        <p v-if="loading" class="text-base text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>
        <p v-if="success" class="text-base text-green-700">{{ success }}</p>

        <template v-if="!loading">
            <div class="app-tabs">
                <button
                    v-for="tpl in templates"
                    :key="tpl.type"
                    type="button"
                    class="app-tab"
                    :class="
                        activeType === tpl.type
                            ? 'border-pink-500 bg-pink-50 text-pink-700'
                            : 'border-slate-300 bg-white text-slate-700'
                    "
                    @click="selectType(tpl.type)"
                >
                    {{ tpl.label }}
                </button>
            </div>

            <div class="app-grid-2">
                <section class="app-panel space-y-3">
                    <h2 class="text-base font-jost-bold text-dark-blue-500">
                        Шаблон HTML
                    </h2>
                    <textarea
                        v-model="body"
                        rows="22"
                        class="app-field font-mono text-base"
                        spellcheck="false"
                    />
                    <div class="app-actions">
                        <input
                            v-model="previewOrderId"
                            type="number"
                            min="1"
                            placeholder="ID заказа для preview"
                            class="app-field max-w-xs"
                        />
                        <button
                            type="button"
                            class="app-btn-secondary"
                            @click="preview"
                        >
                            Preview PDF
                        </button>
                        <button
                            type="button"
                            class="app-btn-primary"
                            :disabled="saving"
                            @click="save"
                        >
                            Сохранить
                        </button>
                    </div>
                </section>

                <section class="app-panel space-y-3">
                    <h2 class="text-base font-jost-bold text-dark-blue-500">
                        Переменные
                    </h2>
                    <ul class="max-h-[28rem] space-y-2 overflow-y-auto text-base">
                        <li
                            v-for="variable in variables"
                            :key="variable.key"
                            class="flex items-start justify-between gap-2 border-b border-slate-200 pb-2"
                        >
                            <div>
                                <p class="font-jost-medium text-slate-800">
                                    {{ variable.label }}
                                </p>
                                <p class="text-slate-500">
                                    {{ formatVar(variable.key) }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 text-pink-600 hover:underline"
                                @click="insertVariable(variable.key)"
                            >
                                Вставить
                            </button>
                        </li>
                    </ul>
                    <div v-if="loops.length" class="space-y-2">
                        <h3 class="font-jost-medium text-dark-blue-500">Циклы</h3>
                        <p
                            v-for="loop in loops"
                            :key="loop.key"
                            class="text-slate-600"
                        >
                            {{ formatLoop(loop) }}
                        </p>
                    </div>
                </section>
            </div>
        </template>
    </div>
</template>
