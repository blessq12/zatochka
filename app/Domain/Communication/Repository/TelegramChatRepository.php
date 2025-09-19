<?php

namespace App\Domain\Communication\Repository;

use App\Domain\Communication\Entity\TelegramChat;

interface TelegramChatRepository
{
    public function findByChatId(string $chatId): ?TelegramChat;
    public function findByUsername(string $username): ?TelegramChat;
    public function save(TelegramChat $telegramChat): void;
    public function delete(int $id): void;
}
