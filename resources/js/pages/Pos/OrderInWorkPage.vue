<template>
    <div class="pos-page-content order-in-work-page">
        <div v-if="isLoading" class="loading">Загрузка...</div>
        <div v-else-if="!order" class="error-state">
            <p>Заказ не найден</p>
            <router-link :to="{ name: backRouteName }" class="btn-back">
                Вернуться к списку
            </router-link>
        </div>
        <div v-else class="order-content">
            <OrderInWorkHeader
                :order="order"
                :back-route-name="backRouteName"
                :is-read-only="isReadOnly"
                :is-changing-status="isChangingStatus"
                :changing-to-status="changingToStatus"
                :is-completing-order="isCompletingOrder"
                :works-count="works.length"
                :in-work-button-label="inWorkButtonLabel"
                :complete-button-title="completeButtonTitle"
                :get-status-label="getStatusLabel"
                :get-status-class="getStatusClass"
                @set-in-work="setInWorkStatus"
                @set-waiting-parts="setWaitingPartsStatus"
                @complete="completeOrder"
            />

            <OrderInWorkContext
                :order="order"
                :equipment-brand-model-line="equipmentBrandModelLineComputed"
                :equipment-serial-rows="equipmentSerialRowsComputed"
                :master-internal-comments="masterInternalComments"
                :is-workspace-editable="isWorkspaceEditable"
                :is-saving-comment="isSavingComment"
                :comment-form="commentForm"
                :format-pos-order-payment-type="formatPosOrderPaymentType"
                :item-label="itemLabel"
                @save-comment="saveComment"
            />

            <OrderInWorkWorks
                :is-repair-order="isRepairOrder"
                :repairable-items="repairableItems"
                :works-by-item="worksByItem"
                :items-without-works="itemsWithoutWorks"
                :works-count="works.length"
                :is-workspace-editable="isWorkspaceEditable"
                :work-drafts="workDrafts"
                :is-adding-work-for-item="isAddingWorkForItem"
                :is-deleting-work="isDeletingWork"
                :is-rejecting-item="isRejectingItem"
                :item-label="itemLabel"
                :works-for-component="worksForComponent"
                @reject-item="rejectItem"
                @delete-work="deleteWork"
                @add-work-for-component="addWorkForComponent"
                @add-work-for-item="addWorkForItem"
            />
        </div>
    </div>
</template>

<script>
import OrderInWorkContext from "./order-in-work/OrderInWorkContext.vue";
import OrderInWorkHeader from "./order-in-work/OrderInWorkHeader.vue";
import OrderInWorkWorks from "./order-in-work/OrderInWorkWorks.vue";
import { useOrderInWorkPage } from "./order-in-work/useOrderInWorkPage.js";

export default {
    name: "OrderInWorkPage",
    components: {
        OrderInWorkHeader,
        OrderInWorkContext,
        OrderInWorkWorks,
    },
    setup() {
        return useOrderInWorkPage();
    },
};
</script>

<style src="./order-in-work/order-in-work.css"></style>
