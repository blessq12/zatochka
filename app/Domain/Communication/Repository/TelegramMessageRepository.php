<?php

namespace App\Domain\Communication\Repository;

use App\Domain\Communication\Entity\TelegramMessage;

interface TelegramMessageRepository
{
    public function create(array $data): TelegramMessage;

    public function findByChatId(int $chatId): array;

    public function findById(int $id): ?TelegramMessage;

    public function delete(int $id): bool;
}
