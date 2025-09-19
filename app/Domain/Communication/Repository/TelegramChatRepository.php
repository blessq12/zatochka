<?php

namespace App\Domain\Communication\Repository;

use App\Domain\Communication\Entity\TelegramChat;

interface TelegramChatRepository
{
    public function findByTelegramId(int $telegramId): ?TelegramChat;

    public function create(array $data): TelegramChat;

    public function findOrCreate(int $telegramId, array $data): TelegramChat;

    public function update(int $id, array $data): TelegramChat;

    public function delete(int $id): bool;
}
