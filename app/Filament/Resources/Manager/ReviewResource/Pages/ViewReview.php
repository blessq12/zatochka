<?php

namespace App\Filament\Resources\Manager\ReviewResource\Pages;

use App\Application\UseCases\Review\UpdateReviewUseCase;
use App\Filament\Resources\Manager\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('Одобрить')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(): bool => !$this->record->is_approved)
                ->action(function () {
                    try {
                        app(UpdateReviewUseCase::class)
                            ->loadData(['id' => $this->record->id, 'is_approved' => true])
                            ->validate()
                            ->execute();

                        Notification::make()
                            ->title('Отзыв одобрен')
                            ->success()
                            ->send();
                        $this->refreshFormData(['is_approved']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка одобрения')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('reject')
                ->label('Отклонить')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(): bool => $this->record->is_approved)
                ->action(function () {
                    try {
                        app(UpdateReviewUseCase::class)
                            ->loadData(['id' => $this->record->id, 'is_approved' => false])
                            ->validate()
                            ->execute();

                        Notification::make()
                            ->title('Отзыв отклонен')
                            ->success()
                            ->send();
                        $this->refreshFormData(['is_approved']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка отклонения')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
