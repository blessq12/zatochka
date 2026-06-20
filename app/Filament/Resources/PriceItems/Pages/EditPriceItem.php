<?php

namespace App\Filament\Resources\PriceItems\Pages;

use App\Application\Catalog\Command\SavePriceItemCommand;
use App\Application\Catalog\CommandHandler\SavePriceItemHandler;
use App\Filament\Resources\PriceItems\PriceItemResource;
use App\Infrastructure\Catalog\Persistence\Eloquent\PriceItemModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPriceItem extends EditRecord
{
    protected static string $resource = PriceItemResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var PriceItemModel $record */
        $item = app(SavePriceItemHandler::class)->handle(new SavePriceItemCommand(
            id: $record->id,
            priceBlockId: (int) $data['price_block_id'],
            name: $data['name'],
            price: (string) $data['price'],
            description: $data['description'] ?? null,
            sortOrder: (int) $data['sort_order'],
        ));

        return PriceItemModel::query()->findOrFail($item->id());
    }
}
