<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\RejectReviewCommand;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\RejectReviewHandler;
use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Filament\Support\ClientReviewPresenter;
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
                    ->color(fn (ReviewStatus $state): string => ClientReviewPresenter::statusColor($state))
                    ->formatStateUsing(fn (ReviewStatus $state): string => ClientReviewPresenter::statusLabel($state)),
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
                    ->action(function (ReviewModel $record): void {
                        app(ApproveReviewHandler::class)->handle(new ApproveReviewCommand(
                            reviewId: (int) $record->getKey(),
                        ));

                        Notification::make()->success()->title('Отзыв одобрен')->send();
                    }),
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::Pending)
                    ->action(function (ReviewModel $record): void {
                        app(RejectReviewHandler::class)->handle(new RejectReviewCommand(
                            reviewId: (int) $record->getKey(),
                        ));

                        Notification::make()->success()->title('Отзыв отклонён')->send();
                    }),
            ]);
    }
}
