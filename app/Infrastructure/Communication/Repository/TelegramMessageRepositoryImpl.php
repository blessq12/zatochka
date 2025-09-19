<?php

namespace App\Infrastructure\Communication\Repository;

use App\Domain\Communication\Entity\TelegramMessage;
use App\Domain\Communication\Repository\TelegramMessageRepository;
use App\Domain\Communication\Mapper\TelegramMessageMapper;
use App\Models\TelegramMessage as TelegramMessageModel;

class TelegramMessageRepositoryImpl implements TelegramMessageRepository
{
    public function __construct(
        private readonly TelegramMessageMapper $mapper
    ) {}

    public function create(array $data): TelegramMessage
    {
        $model = TelegramMessageModel::create([
            'chat_id' => $data['chat_id'],
            'client_id' => $data['client_id'] ?? null,
            'content' => $data['content'],
            'direction' => $data['direction'],
            'sent_at' => $data['sent_at'] ?? now(),
            'is_deleted' => false,
        ]);

        return $this->mapper->toEntity($model);
    }

    public function findByChatId(int $chatId): array
    {
        $models = TelegramMessageModel::where('chat_id', $chatId)
            ->where('is_deleted', false)
            ->orderBy('sent_at')
            ->get();

        return $models->map(fn($model) => $this->mapper->toEntity($model))->toArray();
    }

    public function findById(int $id): ?TelegramMessage
    {
        $model = TelegramMessageModel::where('id', $id)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function delete(int $id): bool
    {
        return TelegramMessageModel::where('id', $id)
            ->update(['is_deleted' => true]) > 0;
    }
}
