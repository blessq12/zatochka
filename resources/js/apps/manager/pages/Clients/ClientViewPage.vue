<script>
import { clientService } from "../../services/ClientService.js";

export default {
    name: "ClientViewPage",
        data() {
        return { client: null };
    },
    async mounted() {
        this.client = await clientService.get(this.$route.params.id);
    },
};
</script>

<template>
    <div v-if="client" class="max-w-2xl bg-white border border-slate-200 p-6 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ client.name || "Без имени" }}</h1>
                    <p class="text-slate-600 mt-1">{{ client.phone }}</p>
                </div>
                <router-link :to="{ name: 'manager.clients.edit', params: { id: client.id } }" class="text-pink-500 font-jost-bold text-sm">Редактировать</router-link>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd>{{ client.email || "—" }}</dd></div>
                <div><dt class="text-slate-500">Бонусы</dt><dd>{{ client.bonusBalance }}</dd></div>
                <div><dt class="text-slate-500">Дата рождения</dt><dd>{{ client.birthDate || "—" }}</dd></div>
                <div><dt class="text-slate-500">Адрес доставки</dt><dd>{{ client.deliveryAddress || "—" }}</dd></div>
            </dl>
        </div>
</template>
