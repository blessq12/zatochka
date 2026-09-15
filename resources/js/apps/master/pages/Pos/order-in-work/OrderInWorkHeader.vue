<template>
    <div class="order-header-section">
        <div class="order-header-top">
            <div class="order-title-group">
                <h1 class="order-title">Заказ №{{ order.order_number }}</h1>
                <div class="order-status-group">
                    <span
                        class="order-status-badge"
                        :class="getStatusClass(order.status)"
                    >
                        {{ getStatusLabel(order.status) }}
                    </span>
                    <span
                        v-if="order.urgency === 'urgent'"
                        class="urgency-badge urgent"
                    >
                        Срочно
                    </span>
                </div>
            </div>
            <router-link :to="{ name: backRouteName }" class="btn-back">
                ← Назад
            </router-link>
        </div>

        <div v-if="!isReadOnly" class="status-actions">
            <button
                @click="$emit('set-in-work')"
                class="btn-status btn-in-work"
                :disabled="isChangingStatus || order.status === 'in_work'"
            >
                <span
                    v-if="isChangingStatus && changingToStatus === 'in_work'"
                    >Сохранение...</span
                >
                <span v-else>{{ inWorkButtonLabel }}</span>
            </button>
            <button
                @click="$emit('set-waiting-parts')"
                class="btn-status btn-waiting-parts"
                :disabled="isChangingStatus || order.status !== 'in_work'"
            >
                <span
                    v-if="
                        isChangingStatus &&
                        changingToStatus === 'waiting_parts'
                    "
                    >Сохранение...</span
                >
                <span v-else>Ожидание запчастей</span>
            </button>
            <button
                @click="$emit('complete')"
                class="btn-status btn-complete"
                :disabled="
                    isCompletingOrder ||
                    order.status !== 'in_work' ||
                    (worksCount === 0 && !canFinishWithoutWorks)
                "
                :title="completeButtonTitle"
            >
                <span v-if="isCompletingOrder">Сохранение...</span>
                <span v-else>Завершить заказ</span>
            </button>
        </div>
        <p v-else class="readonly-hint">
            Заказ готов к выдаче — только просмотр. Изменения через менеджера.
        </p>
        <p
            v-if="order.status === 'waiting_parts'"
            class="waiting-parts-hint"
        >
            Заказ ожидает запчасти. Переведите в работу, чтобы добавить работы
            и комментарии.
        </p>
    </div>
</template>

<script>
export default {
    name: "OrderInWorkHeader",
    props: {
        order: { type: Object, required: true },
        backRouteName: { type: String, required: true },
        isReadOnly: { type: Boolean, required: true },
        isChangingStatus: { type: Boolean, required: true },
        changingToStatus: { type: String, default: null },
        isCompletingOrder: { type: Boolean, required: true },
        worksCount: { type: Number, required: true },
        canFinishWithoutWorks: { type: Boolean, default: false },
        inWorkButtonLabel: { type: String, required: true },
        completeButtonTitle: { type: String, default: "" },
        getStatusLabel: { type: Function, required: true },
        getStatusClass: { type: Function, required: true },
    },
    emits: ["set-in-work", "set-waiting-parts", "complete"],
};
</script>
