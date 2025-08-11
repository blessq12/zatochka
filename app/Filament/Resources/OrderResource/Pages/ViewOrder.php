<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Можно добавить виджеты если нужно
        ];
    }

    protected function getFooterActions(): array
    {
        $order = $this->getRecord();

        return [
            // Подтверждение заказа
            Action::make('confirm')
                ->label('Подтвердить заказ')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn(): bool => $order->status === 'new')
                ->action(function () {
                    $this->getRecord()->confirm();
                    $this->notify('success', 'Заказ подтвержден');
                }),

            // Передача курьеру
            Action::make('assign_courier')
                ->label('Передать курьеру')
                ->icon('heroicon-m-truck')
                ->color('warning')
                ->visible(fn(): bool => in_array($order->status, ['new', 'confirmed']))
                ->action(function () {
                    $this->getRecord()->assignToCourier();
                    $this->notify('success', 'Заказ передан курьеру');
                }),

            // Передача мастеру
            Action::make('assign_master')
                ->label('Передать мастеру')
                ->icon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->visible(fn(): bool => in_array($order->status, ['confirmed', 'courier_pickup']))
                ->action(function () {
                    $this->getRecord()->assignToMaster();
                    $this->notify('success', 'Заказ передан мастеру');
                }),

            // Начать работу
            Action::make('start_work')
                ->label('Начать работу')
                ->icon('heroicon-m-play')
                ->color('warning')
                ->visible(fn(): bool => in_array($order->status, ['master_received', 'confirmed']))
                ->action(function () {
                    $this->getRecord()->startWork();
                    $this->notify('success', 'Работа начата');
                }),

            // Завершить работу
            Action::make('complete_work')
                ->label('Завершить работу')
                ->icon('heroicon-m-check')
                ->color('success')
                ->visible(fn(): bool => in_array($order->status, ['in_progress', 'master_received']))
                ->action(function () {
                    $this->getRecord()->completeWork();
                    $this->notify('success', 'Работа завершена');
                }),

            // Готов к выдаче
            Action::make('mark_ready')
                ->label('Готов к выдаче')
                ->icon('heroicon-m-gift')
                ->color('success')
                ->visible(fn(): bool => in_array($order->status, ['work_completed', 'in_progress']))
                ->action(function () {
                    $this->getRecord()->markAsReady();
                    $this->notify('success', 'Заказ готов к выдаче');
                }),

            // Передать на доставку
            Action::make('assign_delivery')
                ->label('Передать на доставку')
                ->icon('heroicon-m-truck')
                ->color('warning')
                ->visible(fn(): bool => in_array($order->status, ['work_completed', 'ready_for_pickup']))
                ->action(function () {
                    $this->getRecord()->assignToDeliveryCourier();
                    $this->notify('success', 'Заказ передан на доставку');
                }),

            // Доставлен
            Action::make('mark_delivered')
                ->label('Доставлен')
                ->icon('heroicon-m-home')
                ->color('info')
                ->visible(fn(): bool => in_array($order->status, ['courier_delivery', 'ready_for_pickup']))
                ->action(function () {
                    $this->getRecord()->markAsDelivered();
                    $this->notify('success', 'Заказ доставлен');
                }),

            // Получена оплата
            Action::make('receive_payment')
                ->label('Получена оплата')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->visible(fn(): bool => in_array($order->status, ['delivered', 'ready_for_pickup']))
                ->action(function () {
                    $this->getRecord()->receivePayment();
                    $this->notify('success', 'Оплата получена');
                }),

            // Закрыть заказ
            Action::make('close')
                ->label('Закрыть заказ')
                ->icon('heroicon-m-lock-closed')
                ->color('success')
                ->visible(fn(): bool => in_array($order->status, ['payment_received', 'delivered']))
                ->action(function () {
                    $this->getRecord()->close();
                    $this->notify('success', 'Заказ закрыт');
                }),

            // Запросить отзыв
            Action::make('request_feedback')
                ->label('Запросить отзыв')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->color('info')
                ->visible(fn(): bool => in_array($order->status, ['closed', 'payment_received']))
                ->action(function () {
                    $this->getRecord()->requestFeedback();
                    $this->notify('success', 'Запрошен отзыв');
                }),

            // Отменить заказ
            Action::make('cancel')
                ->label('Отменить заказ')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn(): bool => !in_array($order->status, ['closed', 'cancelled', 'feedback_requested']))
                ->requiresConfirmation()
                ->modalHeading('Отменить заказ?')
                ->modalDescription('Вы уверены, что хотите отменить этот заказ? Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Да, отменить')
                ->action(function () {
                    $this->getRecord()->cancel();
                    $this->notify('success', 'Заказ отменен');
                }),
        ];
    }
}
