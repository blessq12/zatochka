<?php

namespace App\Filament\Resources\PriceItems\Pages;

use App\Filament\Support\PriceItemScope;
use Filament\Resources\Pages\CreateRecord;

abstract class CreatePriceItemRecord extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $blockId = (int) $data['price_block_id'];
        $data['sort_order'] = PriceItemScope::nextSortOrder($blockId);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
