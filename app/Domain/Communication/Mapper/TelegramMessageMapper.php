<?php

namespace App\Domain\Communication\Mapper;

use App\Domain\Communication\Entity\TelegramMessage;
use App\Models\TelegramMessage as TelegramMessageModel;

interface TelegramMessageMapper
{
    public function toEntity(TelegramMessageModel $model): TelegramMessage;

    public function toModel(TelegramMessage $entity): TelegramMessageModel;

    public function toArray(TelegramMessage $entity): array;
}
