<script>
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { financeService } from "../../services/FinanceService.js";

export default {
    name: "CashOperationsListPage",
    components: { PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            columns: [
                { key: "id", label: "№" },
                { key: "type", label: "Тип" },
                { key: "amount", label: "Сумма" },
                { key: "comment", label: "Комментарий" },
                { key: "registeredAt", label: "Дата" },
            ],
        };
    },
    async mounted() {
        this.loading = true;
        try {
            const data = await financeService.listCashOperations();
            this.rows = data.items || [];
        } finally {
            this.loading = false;
        }
    },
};
</script>

<template>
    <PageHeader title="Кассовые операции" action-label="Касса" :action-to="{ name: 'manager.cash-desk' }" />
        <DataTable :columns="columns" :rows="rows" :loading="loading" />
</template>
