<?php

namespace App\Infrastructure\Communication\Mapper;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Mapper\TelegramChatMapper;
use App\Models\TelegramChat as TelegramChatModel;

class TelegramChatMapperImpl implements TelegramChatMapper
{
    public function toEntity(TelegramChatModel $model): TelegramChat
    {
        return new TelegramChat(
            $model->id,
            $model->client_id,
            $model->username,
            $model->chat_id,
            $model->is_active,
            $model->metadata ?? [],
            $model->is_deleted
        );
    }

    public function toModel(TelegramChat $entity): TelegramChatModel
    {
        $model = new TelegramChatModel();

        if ($entity->getId()) {
            $model = TelegramChatModel::findOrFail($entity->getId());
        }

        $model->client_id = $entity->getClientId();
        $model->username = $entity->getUsername();
        $model->chat_id = $entity->getChatId();
        $model->is_active = $entity->isActive();
        $model->metadata = $entity->getMetadata();
        $model->is_deleted = $entity->isDeleted();

        return $model;
    }

    public function toArray(TelegramChat $entity): array
    {
        return [
            'id' => $entity->getId(),
            'client_id' => $entity->getClientId(),
            'username' => $entity->getUsername(),
            'chat_id' => $entity->getChatId(),
            'is_active' => $entity->isActive(),
            'metadata' => $entity->getMetadata(),
            'is_deleted' => $entity->isDeleted(),
        ];
    }
}
