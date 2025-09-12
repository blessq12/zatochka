<?php

namespace App\Filament\Resources\Manager\OrderResource\Pages;

use App\Filament\Resources\Manager\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderUrgency;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->using(function () {
                    try {
                        (new \App\Application\UseCases\Order\DeleteOrderUseCase())
                            ->loadData(['id' => $this->record->id])
                            ->validate()
                            ->execute();

                        \Filament\Notifications\Notification::make()
                            ->title('Заказ удален')
                            ->body('Заказ #' . $this->record->order_number . ' успешно удален')
                            ->success()
                            ->send();

                        return redirect($this->getResource()::getUrl('index'));
                    } catch (\App\Domain\Order\Exception\OrderException $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ошибка удаления')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Номер заказа')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('client.full_name')
                            ->label('Клиент')
                            ->url(fn($record) => route('filament.manager.resources.manager.clients.view', $record->client_id)),

                        Infolists\Components\TextEntry::make('type')
                            ->label('Тип услуги')
                            ->formatStateUsing(fn(OrderType $state): string => $state->getLabel())
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Статус')
                            ->formatStateUsing(fn(OrderStatus $state): string => $state->getLabel())
                            ->badge()
                            ->color(fn(OrderStatus $state): string => match ($state) {
                                OrderStatus::NEW => 'gray',
                                OrderStatus::CONSULTATION => 'blue',
                                OrderStatus::DIAGNOSTIC => 'yellow',
                                OrderStatus::IN_WORK => 'warning',
                                OrderStatus::WAITING_PARTS => 'orange',
                                OrderStatus::READY => 'success',
                                OrderStatus::ISSUED => 'success',
                                OrderStatus::CANCELLED => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('urgency')
                            ->label('Срочность')
                            ->formatStateUsing(fn(OrderUrgency $state): string => $state->getLabel())
                            ->badge()
                            ->color(fn(OrderUrgency $state): string => $state->getColor()),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Ответственные')
                    ->schema([
                        Infolists\Components\TextEntry::make('branch.name')
                            ->label('Филиал'),

                        Infolists\Components\TextEntry::make('manager.name')
                            ->label('Менеджер'),

                        Infolists\Components\TextEntry::make('master.name')
                            ->label('Мастер')
                            ->placeholder('Не назначен'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Финансы')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Общая сумма')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('final_price')
                            ->label('Итоговая цена')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('cost_price')
                            ->label('Себестоимость')
                            ->money('RUB')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('profit')
                            ->label('Прибыль')
                            ->money('RUB')
                            ->placeholder('Не рассчитана')
                            ->color(fn($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),

                        Infolists\Components\IconEntry::make('is_paid')
                            ->label('Оплачен')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Дата оплаты')
                            ->dateTime()
                            ->placeholder('Не оплачен')
                            ->visible(fn($record) => $record->is_paid),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Фотографии')
                    ->schema([
                        Infolists\Components\TextEntry::make('before_photos_info')
                            ->label('Фото "До" (что принес клиент)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('before_photos');
                                if ($photos->count() === 0) {
                                    return 'Фотографии не загружены';
                                }

                                $html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                                foreach ($photos as $photo) {
                                    $html .= '<div class="relative">';
                                    $html .= '<img src="' . $photo->getUrl() . '" alt="Фото до" class="w-full h-32 object-cover rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer" onclick="window.open(this.src, \'_blank\')">';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn($record) => $record->getMedia('before_photos')->count() > 0),

                        Infolists\Components\TextEntry::make('after_photos_info')
                            ->label('Фото "После" (результат работ)')
                            ->getStateUsing(function ($record) {
                                $photos = $record->getMedia('after_photos');
                                if ($photos->count() === 0) {
                                    return 'Фотографии не загружены';
                                }

                                $html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                                foreach ($photos as $photo) {
                                    $html .= '<div class="relative">';
                                    $html .= '<img src="' . $photo->getUrl() . '" alt="Фото после" class="w-full h-32 object-cover rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer" onclick="window.open(this.src, \'_blank\')">';
                                    $html .= '<div class="absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">' . $photo->file_name . '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn($record) => $record->getMedia('after_photos')->count() > 0),

                        Infolists\Components\TextEntry::make('no_photos')
                            ->label('')
                            ->state('Фотографии не загружены')
                            ->visible(fn($record) => $record->getMedia('before_photos')->count() === 0 && $record->getMedia('after_photos')->count() === 0)
                            ->color('gray'),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Временные метки')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Обновлен')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
