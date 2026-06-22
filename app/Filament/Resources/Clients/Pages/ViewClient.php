<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\Actions\ClientManageActions;
use App\Filament\Resources\Clients\ClientResource;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public function resolveRecord(int|string $key): Model
    {
        return static::getResource()::getEloquentQuery()
            ->with(['orders', 'reviews.order'])
            ->findOrFail($key);
    }

    public function getTitle(): string|Htmlable
    {
        /** @var ClientModel $record */
        $record = $this->getRecord();

        return $record->full_name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var ClientModel $record */
        $record = $this->getRecord();

        return $record->phone;
    }

    public function refreshClientRecord(): void
    {
        $this->record = $this->resolveRecord($this->getRecord()->getKey());

        $this->cacheSchema('infolist', null);
        $this->cacheSchema('content', null);
    }

    protected function getHeaderActions(): array
    {
        return [
            ClientManageActions::linkGuestOrder(),
            EditAction::make(),
        ];
    }
}
