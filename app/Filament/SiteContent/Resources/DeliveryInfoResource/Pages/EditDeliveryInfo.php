<?php

namespace App\Filament\SiteContent\Resources\DeliveryInfoResource\Pages;

use App\Application\SiteContent\Command\UpdateDeliveryInfoCommand;
use App\Application\SiteContent\Command\UpdateDeliveryInfoHandler;
use App\Filament\SiteContent\Resources\DeliveryInfoResource;
use App\Shared\Domain\DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class EditDeliveryInfo extends EditRecord
{
    protected static string $resource = DeliveryInfoResource::class;

    protected static ?string $title = 'Информация о доставке';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $conditions = $data['free_conditions'] ?? [];
        $data['free_conditions'] = array_map(
            static fn ($condition): array => is_array($condition)
                ? $condition
                : ['condition' => (string) $condition],
            array_values((array) $conditions),
        );

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $conditions = [];

        foreach ((array) ($data['free_conditions'] ?? []) as $row) {
            $conditions[] = is_array($row) ? (string) ($row['condition'] ?? '') : (string) $row;
        }

        try {
            app(UpdateDeliveryInfoHandler::class)->handle(new UpdateDeliveryInfoCommand(
                $conditions,
                array_values((array) ($data['advantages'] ?? [])),
            ));
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
            $this->halt();
        } catch (Throwable $e) {
            Notification::make()->danger()->title('Не удалось сохранить')->send();
            $this->halt();
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Доставка обновлена';
    }
}
