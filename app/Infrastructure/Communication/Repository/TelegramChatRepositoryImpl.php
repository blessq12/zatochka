<?php

namespace App\Infrastructure\Communication\Repository;

use App\Domain\Communication\Entity\TelegramChat;
use App\Domain\Communication\Repository\TelegramChatRepository;
use App\Domain\Communication\Mapper\TelegramChatMapper;
use App\Models\TelegramChat as TelegramChatModel;
use Illuminate\Support\Facades\DB;

class TelegramChatRepositoryImpl implements TelegramChatRepository
{
    public function __construct(
        private readonly TelegramChatMapper $mapper
    ) {}

    public function findByTelegramId(int $telegramId): ?TelegramChat
    {
        $model = TelegramChatModel::where('chat_id', $telegramId)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function findByUsername(string $username): ?TelegramChat
    {
        $model = TelegramChatModel::where('username', $username)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function findByClientId(string $clientId): ?TelegramChat
    {
        $model = TelegramChatModel::where('client_id', $clientId)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->first();

        return $model ? $this->mapper->toEntity($model) : null;
    }

    public function create(array $data): TelegramChat
    {
        $model = TelegramChatModel::create([
            'client_id' => $data['client_id'] ?? null,
            'username' => $data['username'] ?? '',
            'chat_id' => $data['chat_id'],
            'is_active' => $data['is_active'] ?? true,
            'metadata' => $data['metadata'] ?? [],
            'is_deleted' => false,
        ]);

        return $this->mapper->toEntity($model);
    }

    public function findOrCreate(int $telegramId, array $data): TelegramChat
    {
        $existing = $this->findByTelegramId($telegramId);

        if ($existing) {
            return $existing;
        }

        return $this->create([
            'chat_id' => $telegramId,
            'username' => $data['username'] ?? '',
            'is_active' => true,
            'metadata' => $data,
        ]);
    }

    public function update(int $id, array $data): TelegramChat
    {
        $model = TelegramChatModel::findOrFail($id);
        $model->update($data);

        return $this->mapper->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return TelegramChatModel::where('id', $id)
            ->update(['is_deleted' => true]) > 0;
    }
}
