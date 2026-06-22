<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Resources\Clients\Actions\ClientManageActions;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\ClientReviewPresenter;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ReviewModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Данные клиента')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('ФИО')
                            ->weight(FontWeight::Medium),
                        TextEntry::make('phone')
                            ->label('Телефон'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('birth_date')
                            ->label('Дата рождения')
                            ->date('d.m.Y')
                            ->placeholder('—'),
                        TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Регистрация')
                            ->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Заказы клиента')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Номер'),
                                TableColumn::make('Статус'),
                                TableColumn::make('Создан'),
                                TableColumn::make('Итого')->alignment('end'),
                            ])
                            ->schema([
                                TextEntry::make('order_number')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Medium)
                                    ->url(fn (OrderModel $record): string => OrderResource::getUrl('view', ['record' => $record])),
                                TextEntry::make('status')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => OrderViewPresenter::statusColor($record->status))
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->status->label()),
                                TextEntry::make('created_at')
                                    ->hiddenLabel()
                                    ->dateTime('d.m.Y H:i'),
                                TextEntry::make('price')
                                    ->hiddenLabel()
                                    ->money('RUB')
                                    ->placeholder('—'),
                            ])
                            ->placeholder('У клиента пока нет привязанных заказов'),
                    ]),

                Section::make('Отзывы клиента')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        RepeatableEntry::make('reviews')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Заказ'),
                                TableColumn::make('Оценка'),
                                TableColumn::make('Комментарий'),
                                TableColumn::make('Статус'),
                                TableColumn::make('Дата'),
                                TableColumn::make('Действия')->hiddenHeaderLabel(),
                            ])
                            ->schema([
                                TextEntry::make('order.order_number')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Medium)
                                    ->placeholder('—')
                                    ->url(fn (ReviewModel $record): ?string => $record->order
                                        ? OrderResource::getUrl('view', ['record' => $record->order])
                                        : null),
                                TextEntry::make('rating')
                                    ->hiddenLabel(),
                                TextEntry::make('comment')
                                    ->hiddenLabel()
                                    ->placeholder('—')
                                    ->limit(80),
                                TextEntry::make('status')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (ReviewModel $record): string => ClientReviewPresenter::statusColor($record->status))
                                    ->formatStateUsing(fn (ReviewModel $record): string => ClientReviewPresenter::statusLabel($record->status)),
                                TextEntry::make('created_at')
                                    ->hiddenLabel()
                                    ->dateTime('d.m.Y H:i'),
                                Actions::make([
                                    ClientManageActions::approveReview(),
                                    ClientManageActions::rejectReview(),
                                ]),
                            ])
                            ->placeholder('У клиента пока нет отзывов'),
                    ]),
            ]);
    }
}
