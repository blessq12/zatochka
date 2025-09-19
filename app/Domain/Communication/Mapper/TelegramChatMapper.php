<?php

namespace App\Domain\Communication\Mapper;

use App\Domain\Communication\Entity\TelegramChat;
use App\Models\TelegramChat as TelegramChatModel;

interface TelegramChatMapper
{
    public function toDomain(TelegramChatModel $model): TelegramChat;
    public function toModel(TelegramChat $entity): TelegramChatModel;
}
