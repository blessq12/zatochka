<?php

namespace App\Filament\SiteContent\Resources\DeliveryInfoResource\Pages;

use App\Filament\SiteContent\Resources\DeliveryInfoResource;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListDeliveryInfos extends ListRecords
{
    protected static string $resource = DeliveryInfoResource::class;

    protected static ?string $title = 'Доставка';

    public function mount(): void
    {
        $record = DeliveryInfoModel::query()->find(1);

        if ($record instanceof Model) {
            $this->redirect(DeliveryInfoResource::getUrl('edit', ['record' => $record]));
        }
    }
}
