<?php

namespace App\Filament\Feedback\Resources;

use App\Application\Feedback\Command\PublishReviewCommand;
use App\Application\Feedback\Command\PublishReviewHandler;
use App\Application\Feedback\Command\RejectReviewCommand;
use App\Application\Feedback\Command\RejectReviewHandler;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Filament\Feedback\Resources\ReviewResource\Pages\ListReviews;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ReviewResource extends DomainResource
{
    protected static ?string $model = ReviewModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Клиенты';

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', ReviewStatus::PendingModeration->value)
            ->orderBy('submitted_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Оценка')
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Отправлен')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('moderate')
                    ->label('Просмотр')
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading('Модерация отзыва')
                    ->modalWidth('xl')
                    ->fillForm(fn (ReviewModel $record): array => [
                        'order_id' => $record->order_id,
                        'client_id' => $record->client_id,
                        'rating' => $record->rating,
                        'comment' => $record->comment ?: '—',
                        'manager_reply' => $record->manager_reply,
                    ])
                    ->form([
                        TextInput::make('order_id')
                            ->label('Заказ')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('client_id')
                            ->label('Клиент')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('rating')
                            ->label('Оценка')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('comment')
                            ->label('Текст отзыва')
                            ->rows(5)
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('manager_reply')
                            ->label('Ответ менеджера')
                            ->rows(4),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('accept', arguments: ['decision' => 'accept'])
                            ->label('Принять')
                            ->color('success'),
                        $action->makeModalSubmitAction('reject', arguments: ['decision' => 'reject'])
                            ->label('Отклонить')
                            ->color('danger'),
                    ])
                    ->action(function (ReviewModel $record, array $data, array $arguments): void {
                        $moderatorId = (int) auth()->id();

                        if ($moderatorId <= 0) {
                            Notification::make()
                                ->title('Не удалось определить менеджера')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            if (($arguments['decision'] ?? null) === 'accept') {
                                app(PublishReviewHandler::class)->handle(new PublishReviewCommand(
                                    (int) $record->id,
                                    $moderatorId,
                                    filled($data['manager_reply'] ?? null) ? (string) $data['manager_reply'] : null,
                                ));
                                Notification::make()->title('Отзыв опубликован')->success()->send();

                                return;
                            }

                            if (($arguments['decision'] ?? null) === 'reject') {
                                app(RejectReviewHandler::class)->handle(new RejectReviewCommand(
                                    (int) $record->id,
                                    $moderatorId,
                                ));
                                Notification::make()->title('Отзыв отклонён')->success()->send();
                            }
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }
}
