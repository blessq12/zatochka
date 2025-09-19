<?php

namespace App\Infrastructure\Communication\Mapper;

use App\Domain\Communication\Entity\TelegramMessage;
use App\Domain\Communication\Mapper\TelegramMessageMapper;
use App\Models\TelegramMessage as TelegramMessageModel;

class TelegramMessageMapperImpl implements TelegramMessageMapper
{
    public function toEntity(TelegramMessageModel $model): TelegramMessage
    {
        return new TelegramMessage(
            $model->id,
            $model->chat_id,
            $model->client_id,
            $model->content,
            $model->direction,
            $model->sent_at,
            $model->is_deleted
        );
    }

    public function toModel(TelegramMessage $entity): TelegramMessageModel
    {
        $model = new TelegramMessageModel();

        if ($entity->getId()) {
            $model = TelegramMessageModel::findOrFail($entity->getId());
        }

        $model->chat_id = $entity->getChatId();
        $model->client_id = $entity->getClientId();
        $model->content = $entity->getContent();
        $model->direction = $entity->getDirection();
        $model->sent_at = $entity->getSentAt();
        $model->is_deleted = $entity->isDeleted();

        return $model;
    }

    public function toArray(TelegramMessage $entity): array
    {
        return [
            'id' => $entity->getId(),
            'chat_id' => $entity->getChatId(),
            'client_id' => $entity->getClientId(),
            'content' => $entity->getContent(),
            'direction' => $entity->getDirection(),
            'sent_at' => $entity->getSentAt()->format('Y-m-d H:i:s'),
            'is_deleted' => $entity->isDeleted(),
        ];
    }
}
