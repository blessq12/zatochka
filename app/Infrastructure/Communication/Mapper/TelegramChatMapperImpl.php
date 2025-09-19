<?php

namespace App\Infrastructure\Communication\Mapper;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Mapper\TelegramChatMapper;
use App\Models\TelegramChat as TelegramChatModel;

class TelegramChatMapperImpl implements TelegramChatMapper
{
    public function toDomain(TelegramChatModel $model): TelegramChat
    {
        return new TelegramChat(
            id: $model->id,
            clientId: $model->client_id,
            username: $model->username,
            chatId: $model->chat_id,
            isActive: $model->is_active,
            metadata: $model->metadata ?? [],
            isDeleted: $model->is_deleted
        );
    }

    public function toModel(TelegramChat $entity): TelegramChatModel
    {
        $model = new TelegramChatModel();

        if ($entity->id > 0) {
            $model = TelegramChatModel::find($entity->id);
        }

        $model->client_id = $entity->clientId;
        $model->username = $entity->username;
        $model->chat_id = $entity->chatId;
        $model->is_active = $entity->isActive;
        $model->metadata = $entity->metadata;
        $model->is_deleted = $entity->isDeleted;

        return $model;
    }
}
