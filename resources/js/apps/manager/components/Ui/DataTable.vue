<script>
export default {
    name: "DataTable",
    props: {
        columns: { type: Array, required: true },
        rows: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
        emptyText: { type: String, default: "Нет данных" },
    },
};
</script>

<template>
    <div class="bg-white border border-slate-200 overflow-hidden">
        <div v-if="loading" class="p-8 text-slate-500 text-sm">Загрузка...</div>
        <div v-else-if="!rows.length" class="p-8 text-slate-500 text-sm">{{ emptyText }}</div>
        <table v-else class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        class="text-left px-4 py-3 font-jost-bold text-slate-600"
                    >
                        {{ col.label }}
                    </th>
                    <th v-if="$slots.actions" class="px-4 py-3 w-28"></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, idx) in rows"
                    :key="row.id ?? idx"
                    class="border-b border-slate-100 hover:bg-slate-50"
                >
                    <td v-for="col in columns" :key="col.key" class="px-4 py-3 align-top text-slate-800">
                        <slot :name="`cell-${col.key}`" :row="row">
                            {{ row[col.key] }}
                        </slot>
                    </td>
                    <td v-if="$slots.actions" class="px-4 py-3 align-top">
                        <div class="flex items-center gap-2 justify-end">
                            <slot name="actions" :row="row" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
