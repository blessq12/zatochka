<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class MasterDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Мастерская';
    protected static ?string $title = 'POS Панель мастера';
    protected static ?string $slug = 'master-dashboard';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public $selectedOrder = null;
    public $filterStatus = 'all';
    public $searchQuery = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('filterStatus')
                    ->label('Статус заказа')
                    ->options([
                        'all' => 'Все заказы',
                        'master_received' => 'Принят мастером',
                        'in_work' => 'В работе',
                        'ready' => 'Готов',
                        'courier_delivery' => 'Передан курьеру',
                    ])
                    ->default('all')
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->dispatch('refreshOrders')),

                TextInput::make('searchQuery')
                    ->label('Поиск по номеру заказа')
                    ->placeholder('Введите номер заказа...')
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->dispatch('refreshOrders')),
            ]);
    }

    public function getOrders()
    {
        $query = Order::with(['client', 'orderTools'])
            ->whereIn('status', ['master_received', 'in_work', 'ready', 'courier_delivery']);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->searchQuery) {
            $query->where('order_number', 'like', '%' . $this->searchQuery . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrder = Order::with(['client', 'orderTools', 'repairs'])->find($orderId);
        $this->dispatch('orderSelected', orderId: $orderId);
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::find($orderId);

        if (!$order) {
            Notification::make()->danger()->title('Заказ не найден')->send();
            return;
        }

        switch ($newStatus) {
            case 'in_work':
                $order->startWork();
                break;
            case 'ready':
                $order->completeWork();
                break;
            case 'courier_delivery':
                $order->assignToDeliveryCourier();
                break;
        }

        $this->selectedOrder = $order->fresh(['client', 'orderTools', 'repairs']);

        Notification::make()
            ->success()
            ->title('Статус обновлен')
            ->body("Заказ {$order->order_number} переведен в статус: {$newStatus}")
            ->send();

        $this->dispatch('refreshOrders');
    }

    public function saveWorkData($data)
    {
        if (!$this->selectedOrder) {
            Notification::make()->danger()->title('Заказ не выбран')->send();
            return;
        }

        // Сохраняем описание работы
        $this->selectedOrder->update([
            'work_description' => $data['workDescription'] ?? '',
            'discount_percent' => $data['discountPercent'] ?? 0,
            'discount_amount' => $data['discountAmount'] ?? 0,
            'final_price' => $data['finalPrice'] ?? $this->selectedOrder->total_amount,
        ]);

        // Здесь можно добавить сохранение использованных материалов
        // в отдельную таблицу, если нужно

        Notification::make()
            ->success()
            ->title('Данные сохранены')
            ->body('Описание работы и скидка сохранены')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Обновить')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->dispatch('refreshOrders')),
        ];
    }

    protected static string $view = 'filament.pages.master-dashboard';

    protected function getViewData(): array
    {
        return [
            'orders' => $this->getOrders(),
            'selectedOrder' => $this->selectedOrder,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MasterStats::class,
        ];
    }
}
