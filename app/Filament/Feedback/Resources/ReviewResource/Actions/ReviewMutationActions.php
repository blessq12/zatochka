<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Actions;

use App\Application\Feedback\Command\PublishReviewCommand;
use App\Application\Feedback\Command\PublishReviewHandler;
use App\Application\Feedback\Command\RejectReviewCommand;
use App\Application\Feedback\Command\RejectReviewHandler;
use App\Application\Feedback\Command\SetReviewManagerReplyCommand;
use App\Application\Feedback\Command\SetReviewManagerReplyHandler;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class ReviewMutationActions
{
    /** @return list<Action> */
    public static function all(): array
    {
        return [
            self::accept(),
            self::reject(),
            self::addReply(),
        ];
    }

    public static function accept(): Action
    {
        return Action::make('acceptReview')
            ->label('Принять')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::PendingModeration->value)
            ->requiresConfirmation()
            ->modalHeading('Принять отзыв')
            ->modalDescription('Отзыв будет опубликован.')
            ->action(function (ReviewModel $record): void {
                $moderatorId = (int) auth()->id();

                if ($moderatorId <= 0) {
                    Notification::make()->title('Не удалось определить менеджера')->danger()->send();

                    return;
                }

                try {
                    app(PublishReviewHandler::class)->handle(new PublishReviewCommand(
                        (int) $record->id,
                        $moderatorId,
                        $record->manager_reply !== null ? (string) $record->manager_reply : null,
                    ));
                    Notification::make()->title('Отзыв опубликован')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    public static function reject(): Action
    {
        return Action::make('rejectReview')
            ->label('Отклонить')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->visible(fn (ReviewModel $record): bool => $record->status === ReviewStatus::PendingModeration->value)
            ->requiresConfirmation()
            ->modalHeading('Отклонить отзыв')
            ->action(function (ReviewModel $record): void {
                $moderatorId = (int) auth()->id();

                if ($moderatorId <= 0) {
                    Notification::make()->title('Не удалось определить менеджера')->danger()->send();

                    return;
                }

                try {
                    app(RejectReviewHandler::class)->handle(new RejectReviewCommand(
                        (int) $record->id,
                        $moderatorId,
                    ));
                    Notification::make()->title('Отзыв отклонён')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    public static function addReply(): Action
    {
        return Action::make('addReply')
            ->label(fn (ReviewModel $record): string => self::hasManagerReply($record)
                ? 'Обновить ответ'
                : 'Добавить ответ')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('primary')
            ->visible(fn (ReviewModel $record): bool => in_array(
                $record->status,
                [ReviewStatus::PendingModeration->value, ReviewStatus::Published->value],
                true,
            ))
            ->modalHeading(fn (ReviewModel $record): string => self::hasManagerReply($record)
                ? 'Обновить ответ'
                : 'Добавить ответ')
            ->modalSubmitActionLabel(fn (ReviewModel $record): string => self::hasManagerReply($record)
                ? 'Обновить'
                : 'Сохранить')
            ->fillForm(fn (ReviewModel $record): array => [
                'manager_reply' => $record->manager_reply,
            ])
            ->form([
                Textarea::make('manager_reply')
                    ->label('Ответ')
                    ->rows(4)
                    ->required()
                    ->maxLength(2000),
            ])
            ->action(function (ReviewModel $record, array $data): void {
                $wasUpdate = self::hasManagerReply($record);

                try {
                    app(SetReviewManagerReplyHandler::class)->handle(new SetReviewManagerReplyCommand(
                        (int) $record->id,
                        (string) $data['manager_reply'],
                    ));
                    Notification::make()
                        ->title($wasUpdate ? 'Ответ обновлён' : 'Ответ добавлен')
                        ->success()
                        ->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    private static function hasManagerReply(ReviewModel $record): bool
    {
        return trim((string) ($record->manager_reply ?? '')) !== '';
    }
}
