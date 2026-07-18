<?php

namespace App\Filament\CRM\Resources\ClientResource\Support;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Feedback\Resources\ReviewResource;
use App\Filament\Feedback\Resources\ReviewResource\Support\ReviewPresentation;
use App\Filament\Order\Resources\OrderResource;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ClientInfolist
{
    /** @return list<Component> */
    public static function components(): array
    {
        return [
            self::profileSection(),
            self::ordersSection(),
            self::equipmentSection(),
            self::reviewsSection(),
        ];
    }

    private static function profileSection(): Section
    {
        return Section::make('Профиль')
            ->icon(Heroicon::OutlinedUserCircle)
            ->columnSpanFull()
            ->schema([
                Grid::make(2)->schema([
                    TextEntry::make('name')
                        ->label('ФИО')
                        ->placeholder('Без имени')
                        ->weight(FontWeight::SemiBold),
                    TextEntry::make('phone')
                        ->label('Телефон'),
                    TextEntry::make('email')
                        ->label('Эл. почта')
                        ->placeholder('—'),
                    TextEntry::make('birth_date')
                        ->label('Дата рождения')
                        ->date('d.m.Y')
                        ->placeholder('—'),
                    TextEntry::make('delivery_address')
                        ->label('Адрес доставки')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    TextEntry::make('bonus_balance')
                        ->label('Бонусы')
                        ->formatStateUsing(fn(?string $state): string => filled($state) ? (string) $state : '0'),
                    TextEntry::make('created_at')
                        ->label('Создан')
                        ->dateTime('d.m.Y H:i'),
                ]),
            ]);
    }

    private static function ordersSection(): Section
    {
        return Section::make('Заказы')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->columnSpanFull()
            ->schema([
                self::ordersEntry(),
            ]);
    }

    private static function ordersEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('orders')
            ->label('Заказы клиента')
            ->columnSpanFull()
            ->table([
                TableColumn::make('Номер'),
                TableColumn::make('Статус'),
                TableColumn::make('Тип'),
                TableColumn::make('Вид'),
                TableColumn::make('Стоимость'),
                TableColumn::make('Создан'),
            ])
            ->schema([
                TextEntry::make('number')
                    ->state(fn(OrderModel $order): string => ClientPresentation::orderNumberLabel($order))
                    ->url(fn(OrderModel $order): string => OrderResource::getUrl('view', ['record' => $order->id]))
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => OrderStatus::tryLabel($state) ?? ($state ?? '—'))
                    ->color(fn(?string $state): string => OrderStatus::tryColor($state)),
                TextEntry::make('service_type')
                    ->formatStateUsing(fn(?string $state): string => OrderServiceType::tryLabel($state) ?? ($state ?? '—')),
                TextEntry::make('billing_type')
                    ->formatStateUsing(fn(?string $state): string => OrderBillingType::tryLabel($state) ?? ($state ?? '—')),
                TextEntry::make('estimated_amount')
                    ->state(fn(OrderModel $order): string => filled($order->estimated_amount)
                        ? (string) $order->estimated_amount . ' ' . ((string) ($order->estimated_currency ?: 'RUB'))
                        : '—')
                    ->alignEnd(),
                TextEntry::make('created_at')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->placeholder('У клиента пока нет заказов');
    }

    private static function equipmentSection(): Section
    {
        return Section::make('Оборудование')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->columnSpanFull()
            ->schema([
                self::equipmentEntry(),
            ]);
    }

    private static function equipmentEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('equipment')
            ->label('Оборудование клиента')
            ->columnSpanFull()
            ->table([
                TableColumn::make('Название'),
                TableColumn::make('Марка / модель'),
                TableColumn::make('Компоненты'),
                TableColumn::make('Заметки'),
            ])
            ->schema([
                TextEntry::make('title')
                    ->state(fn(ClientEquipmentModel $equipment): string => ClientPresentation::equipmentLabel($equipment))
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('details')
                    ->state(fn(ClientEquipmentModel $equipment): string => ClientPresentation::equipmentDetails($equipment)),
                TextEntry::make('components')
                    ->state(fn(ClientEquipmentModel $equipment): string => ClientPresentation::equipmentComponentsSummary($equipment)),
                TextEntry::make('notes')
                    ->placeholder('—'),
            ])
            ->placeholder('Оборудование не зарегистрировано');
    }

    private static function reviewsSection(): Section
    {
        return Section::make('Отзывы')
            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->columnSpanFull()
            ->schema([
                self::reviewsEntry(),
            ]);
    }

    private static function reviewsEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('reviews')
            ->label('Отзывы клиента')
            ->columnSpanFull()
            ->table([
                TableColumn::make('Заказ'),
                TableColumn::make('Оценка'),
                TableColumn::make('Статус'),
                TableColumn::make('Ответ'),
                TableColumn::make('Отправлен'),
            ])
            ->schema([
                TextEntry::make('order')
                    ->state(fn(ReviewModel $review): string => ReviewPresentation::orderNumberLabel($review))
                    ->url(fn(ReviewModel $review): string => ReviewResource::getUrl('view', ['record' => $review->id]))
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('rating')
                    ->html()
                    ->state(fn(ReviewModel $review): Htmlable => ReviewPresentation::ratingStarsHtml((int) ($review->rating ?? 0))),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => ReviewPresentation::statusLabel($state))
                    ->color(fn(?string $state): string => ReviewPresentation::statusColor($state)),
                TextEntry::make('manager_reply')
                    ->state(fn(ReviewModel $review): string => ReviewPresentation::hasManagerReply($review)
                        ? 'Есть'
                        : 'Нет')
                    ->badge()
                    ->color(fn(ReviewModel $review): string => ReviewPresentation::hasManagerReply($review)
                        ? 'info'
                        : 'gray'),
                TextEntry::make('submitted_at')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->placeholder('Отзывов пока нет');
    }
}
