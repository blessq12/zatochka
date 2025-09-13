<?php

namespace App\Filament\Resources\Manager\ReviewResource\Pages;

use App\Application\UseCases\Review\UpdateReviewUseCase;
use App\Application\UseCases\Review\DeleteReviewUseCase;
use App\Filament\Resources\Manager\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->using(function () {
                    try {
                        app(DeleteReviewUseCase::class)
                            ->loadData(['id' => $this->record->id])
                            ->validate()
                            ->execute();

                        Notification::make()
                            ->title('Отзыв удален')
                            ->success()
                            ->send();

                        return redirect(static::getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка удаления')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            $data['id'] = $this->record->id;
            app(UpdateReviewUseCase::class)
                ->loadData($data)
                ->validate()
                ->execute();

            return $data;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка обновления')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
