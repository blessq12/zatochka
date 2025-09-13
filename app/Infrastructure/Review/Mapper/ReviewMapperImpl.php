<?php

namespace App\Infrastructure\Review\Mapper;

use App\Domain\Review\Entity\Review as ReviewEntity;
use App\Domain\Review\Mapper\ReviewMapper;
use App\Models\Review;

class ReviewMapperImpl implements ReviewMapper
{
    public function toDomain(Review $model): ReviewEntity
    {
        return new ReviewEntity(
            id: $model->id,
            clientId: $model->client_id,
            orderId: $model->order_id,
            rating: (int) $model->rating,
            comment: $model->comment,
            isApproved: (bool) $model->is_approved,
            reply: $model->reply,
            metadata: $model->metadata ?? [],
            isDeleted: (bool) $model->is_deleted,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    public function toEloquent(ReviewEntity $entity): Review
    {
        $model = new Review();
        $model->id = $entity->getId();
        $model->client_id = $entity->getClientId();
        $model->order_id = $entity->getOrderId();
        $model->rating = $entity->getRating();
        $model->comment = $entity->getComment();
        $model->is_approved = $entity->isApproved();
        $model->reply = $entity->getReply();
        $model->metadata = $entity->getMetadata();
        $model->is_deleted = $entity->isDeleted();
        $model->created_at = $entity->getCreatedAt();
        $model->updated_at = $entity->getUpdatedAt();

        return $model;
    }

    public function fromArray(array $data): ReviewEntity
    {
        return new ReviewEntity(
            id: $data['id'] ?? null,
            clientId: $data['client_id'],
            orderId: $data['order_id'],
            rating: (int) $data['rating'],
            comment: $data['comment'],
            isApproved: (bool) ($data['is_approved'] ?? false),
            reply: $data['reply'] ?? null,
            metadata: $data['metadata'] ?? [],
            isDeleted: (bool) ($data['is_deleted'] ?? false),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }
}
