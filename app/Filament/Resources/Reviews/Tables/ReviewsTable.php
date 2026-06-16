<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\RejectReviewCommand;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\RejectReviewHandler;
use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ReviewModel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_id')->label('Заказ #'),
                TextColumn::make('rating')->label('Оценка'),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (ReviewStatus $state): string => match ($state) {
                        ReviewStatus::Pending => 'На модерации',
                        ReviewStatus::Approved => 'Одобрен',
                        ReviewStatus::Rejected => 'Отклонён',
                    }),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::Pending)
                    ->action(function (ReviewModel $record, ApproveReviewHandler $handler): void {
                        $handler->handle(new ApproveReviewCommand($record->id));

                        Notification::make()->success()->title('Отзыв одобрен')->send();
                    }),
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::Pending)
                    ->action(function (ReviewModel $record, RejectReviewHandler $handler): void {
                        $handler->handle(new RejectReviewCommand($record->id));

                        Notification::make()->success()->title('Отзыв отклонён')->send();
                    }),
            ]);
    }
}
