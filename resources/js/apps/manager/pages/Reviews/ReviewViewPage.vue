<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import { reviewService } from "../../services/ReviewService.js";

export default {
    name: "ReviewViewPage",
    components: { ManagerLayout },
    data() {
        return { review: null, reply: "", error: null };
    },
    async mounted() {
        this.review = await reviewService.get(this.$route.params.id);
        this.reply = this.review.managerReply || "";
    },
    methods: {
        async saveReply() {
            try {
                this.review = await reviewService.reply(this.review.id, this.reply);
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка";
            }
        },
        async publish() {
            this.review = await reviewService.publish(this.review.id);
        },
        async reject() {
            this.review = await reviewService.reject(this.review.id);
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Отзыв</template>
        <div v-if="review" class="max-w-2xl bg-white border border-slate-200 p-6 space-y-4">
            <h1 class="text-xl font-jost-bold text-dark-blue-500">Отзыв #{{ review.id }}</h1>
            <p>Оценка: {{ review.rating }}/5</p>
            <p class="text-slate-700">{{ review.comment }}</p>
            <div v-if="error" class="bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
            <textarea v-model="reply" rows="3" class="w-full border border-slate-300 px-3 py-2" placeholder="Ответ менеджера" />
            <div class="flex gap-2">
                <button type="button" class="bg-dark-blue-500 text-white px-4 py-2 text-sm" @click="saveReply">Сохранить ответ</button>
                <button type="button" class="bg-green-600 text-white px-4 py-2 text-sm" @click="publish">Опубликовать</button>
                <button type="button" class="bg-red-600 text-white px-4 py-2 text-sm" @click="reject">Отклонить</button>
            </div>
        </div>
    </ManagerLayout>
</template>
