<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ReviewResource::mutateFormDataBeforeUpdate($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // При загрузке данных для редактирования, устанавливаем target_model и target_record
        if (isset($data['entity_type']) && isset($data['entity_id'])) {
            $data['target_model'] = $data['entity_type'];
            $data['target_record'] = $data['entity_id'];
        }

        return $data;
    }
}
