<?php

namespace App\Infrastructure\Communication\Repository;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Repository\TelegramChatRepository;
use App\Domain\Communication\Mapper\TelegramChatMapper;
use App\Models\TelegramChat as TelegramChatModel;

class TelegramChatRepositoryImpl implements TelegramChatRepository
{
    public function __construct(
        private TelegramChatMapper $mapper
    ) {}

    public function findByChatId(string $chatId): ?TelegramChat
    {
        $model = TelegramChatModel::where('chat_id', $chatId)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByUsername(string $username): ?TelegramChat
    {
        $model = TelegramChatModel::where('username', $username)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(TelegramChat $telegramChat): void
    {
        $model = $this->mapper->toModel($telegramChat);
        $model->save();
    }

    public function delete(int $id): void
    {
        TelegramChatModel::where('id', $id)
            ->update(['is_deleted' => true]);
    }
}
