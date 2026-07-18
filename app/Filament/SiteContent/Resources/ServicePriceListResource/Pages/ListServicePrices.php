<?php

namespace App\Filament\SiteContent\Resources\ServicePriceListResource\Pages;

use App\Domain\SiteContent\VO\ServicePriceCategory;
use App\Filament\SiteContent\Resources\ServicePriceListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListServicePrices extends ListRecords
{
    protected static string $resource = ServicePriceListResource::class;

    protected static ?string $title = 'Прайс услуг';

    public function getTabs(): array
    {
        return [
            ServicePriceCategory::Sharpening->value => Tab::make(ServicePriceCategory::Sharpening->label())
                ->icon(Heroicon::OutlinedScissors)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where(
                    'category',
                    ServicePriceCategory::Sharpening->value,
                )),
            ServicePriceCategory::Repair->value => Tab::make(ServicePriceCategory::Repair->label())
                ->icon(Heroicon::OutlinedWrenchScrewdriver)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where(
                    'category',
                    ServicePriceCategory::Repair->value,
                )),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return ServicePriceCategory::Sharpening->value;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить позицию')
                ->modalHeading('Новая позиция прайса')
                ->fillForm(fn (): array => [
                    'category' => $this->activeTab ?: ServicePriceCategory::Sharpening->value,
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['id'] = $this->nextId();
                    $data['sort_order'] = 0;

                    return $data;
                }),
        ];
    }

    private function nextId(): int
    {
        return DB::transaction(function (): int {
            $row = DB::table('entity_id_sequences')
                ->where('name', 'site_service_price')
                ->lockForUpdate()
                ->first();

            $floor = (int) (DB::table('site_service_prices')->max('id') ?? 0) + 1;
            $value = $row === null ? $floor : max((int) $row->next_value, $floor);

            if ($row === null) {
                DB::table('entity_id_sequences')->insert([
                    'name' => 'site_service_price',
                    'next_value' => $value + 1,
                ]);
            } else {
                DB::table('entity_id_sequences')
                    ->where('name', 'site_service_price')
                    ->update(['next_value' => $value + 1]);
            }

            return $value;
        });
    }
}
