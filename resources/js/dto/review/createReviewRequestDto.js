// DTO: Create review request payload
// Usage: import createReviewRequestDto from "./createReviewRequestDto";
//        const payload = createReviewRequestDto({ orderId, rating, comment });

export default function createReviewRequestDto({
    orderId = null,
    rating = 1,
    comment = "",
} = {}) {
    return {
        order_id: orderId,
        rating: rating,
        comment: comment,
    };
}
