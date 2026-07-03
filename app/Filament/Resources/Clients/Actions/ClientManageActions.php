<?php

namespace App\Filament\Resources\Clients\Actions;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\LinkGuestOrderToClientCommand;
use App\Application\ClientPortal\Command\RejectReviewCommand;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\LinkGuestOrderToClientHandler;
use App\Application\ClientPortal\CommandHandler\RejectReviewHandler;
use App\Application\ClientPortal\Presenter\LinkableGuestOrderPresenter;
use App\Application\ClientPortal\Query\GetLinkableGuestOrdersQuery;
use App\Application\ClientPortal\QueryHandler\GetLinkableGuestOrdersQueryHandler;
use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ReviewModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

final class ClientManageActions
{
    public static function linkGuestOrder(): Action
    {
        return Action::make('linkGuestOrder')
            ->label('Привязать')
            ->icon('heroicon-o-link')
            ->modalHeading('Привязать гостевой заказ')
            ->modalDescription('Выберите гостевой заказ без привязки к ЛК. Телефон в снимке может не совпадать с клиентом.')
            ->form([
                Select::make('order_id')
                    ->label('Гостевой заказ')
                    ->options(fn (): array => LinkableGuestOrderPresenter::options(
                        app(GetLinkableGuestOrdersQueryHandler::class)->handle(new GetLinkableGuestOrdersQuery),
                    ))
                    ->getOptionLabelUsing(function (mixed $value): string {
                        $order = OrderModel::query()->find($value);

                        if ($order === null) {
                            return (string) $value;
                        }

                        return sprintf(
                            '%s · %s · %s',
                            $order->order_number,
                            OrderViewPresenter::clientDisplayName($order),
                            OrderViewPresenter::clientPhone($order) ?? '—',
                        );
                    })
                    ->searchable()
                    ->required(),
            ])
            ->action(function (ClientModel $record, array $data, ViewClient $livewire): void {
                app(LinkGuestOrderToClientHandler::class)->handle(new LinkGuestOrderToClientCommand(
                    clientId: (int) $record->getKey(),
                    orderId: (int) $data['order_id'],
                ));

                $livewire->refreshClientRecord();

                Notification::make()
                    ->success()
                    ->title('Заказ привязан к клиенту')
                    ->send();
            });
    }

    public static function approveReview(): Action
    {
        return Action::make('approveReview')
            ->label('Опубликовать')
            ->icon('heroicon-o-check')
            ->color('success')
            ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::Pending)
            ->action(function (ReviewModel $record, ViewClient $livewire): void {
                app(ApproveReviewHandler::class)->handle(new ApproveReviewCommand(
                    reviewId: (int) $record->getKey(),
                    clientId: (int) $livewire->getRecord()->getKey(),
                ));

                $livewire->refreshClientRecord();

                Notification::make()
                    ->success()
                    ->title('Отзыв опубликован')
                    ->send();
            });
    }

    public static function rejectReview(): Action
    {
        return Action::make('rejectReview')
            ->label('Не публиковать')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::Pending)
            ->action(function (ReviewModel $record, ViewClient $livewire): void {
                app(RejectReviewHandler::class)->handle(new RejectReviewCommand(
                    reviewId: (int) $record->getKey(),
                    clientId: (int) $livewire->getRecord()->getKey(),
                ));

                $livewire->refreshClientRecord();

                Notification::make()
                    ->success()
                    ->title('Отзыв не будет опубликован')
                    ->send();
            });
    }
}
