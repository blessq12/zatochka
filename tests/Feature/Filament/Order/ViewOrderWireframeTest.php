<?php

namespace Tests\Feature\Filament\Order;

use App\Application\Finance\ReadPort\PaymentReadPort;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class ViewOrderWireframeTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_view_order_renders_bc_sections_and_actions_rail(): void
    {
        $this->actingAs($this->manager());

        $orderId = OrderId::generate()->value;
        $clientId = $this->registerClient('View Client', '+79990001100');

        app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            orderId: $orderId,
            clientId: $clientId,
            estimatedAmount: '1000.00',
            items: [
                new CreateOrderItemDTO(
                    toolName: 'Кухонный нож',
                    toolType: 'kitchen_knife',
                    quantity: 1,
                ),
            ],
            serviceType: 'sharpening',
            billingType: 'paid',
            urgency: 'normal',
        ));

        Livewire::test(ViewOrder::class, ['record' => $orderId])
            ->assertSuccessful()
            ->assertSee('Сводка')
            ->assertSee('Параметры')
            ->assertSee('Финальная цена')
            ->assertSee('Стоимость работ')
            ->assertSee('Стоимость запчастей')
            ->assertSee('цены не указаны')
            ->assertSeeHtml('fi-badge')
            ->assertDontSee('статус:')
            ->assertSee('Состав')
            ->assertSee('Мастерская')
            ->assertSee('Склад')
            ->assertSee('Оплата')
            ->assertSee('Доставка')
            ->assertSee('Служебное')
            ->assertSee('Действия')
            ->assertDontSee('Цены')
            ->assertSee('Платежа нет')
            ->assertSee('Без доставки')
            ->assertSee('Материалы не списывались')
            ->assertSee('Отменить')
            ->assertSee('Назначить')
            ->assertDontSee('Нет активных действий')
            ->assertDontSee('Команды по bounded contexts')
            ->assertDontSee('Material lines')
            ->assertDontSee('Work lines')
            ->assertDontSee('Клиент и приёмка')
            ->assertActionExists('backToList');
    }

    public function test_actions_rail_shows_empty_state_when_no_visible_commands(): void
    {
        $this->actingAs($this->manager());

        $master = $this->createMaster('master-empty-actions@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        OrderModel::query()->whereKey($flow['orderId'])->update([
            'status' => OrderStatus::InProgress->value,
        ]);

        Livewire::test(ViewOrder::class, ['record' => $flow['orderId']])
            ->assertSuccessful()
            ->assertSee('Действия')
            ->assertSee('Нет активных действий')
            ->assertDontSee('Команды по bounded contexts')
            ->assertDontSee('Отменить');
    }

    public function test_finance_and_delivery_flag_feed_order_view_blocks(): void
    {
        $this->actingAs($this->manager());

        $master = $this->createMaster('master-view@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);
        $orderId = (string) $flow['orderId'];

        $order = OrderModel::query()->whereKey($orderId)->firstOrFail();
        $order->update(['delivery_required' => true]);

        ClientModel::query()->whereKey($order->client_id)->update([
            'delivery_address' => 'Москва, Тверская, 1, кв. 10',
        ]);

        PaymentModel::query()->create([
            'id' => 9001,
            'number' => 'PMT-26-1',
            'order_id' => $orderId,
            'amount' => '150.00',
            'currency' => 'RUB',
            'method' => 'cash',
            'accepted_at' => now(),
        ]);

        $payment = app(PaymentReadPort::class)->findByOrderId($orderId);
        $this->assertNotNull($payment);
        $this->assertSame('PMT-26-1', $payment->number);

        Livewire::test(ViewOrder::class, ['record' => $orderId])
            ->assertSuccessful()
            ->assertSee('PMT-26-1')
            ->assertSee('Нужна доставка')
            ->assertSee('Москва, Тверская, 1, кв. 10')
            ->assertDontSee('Платежа нет')
            ->assertDontSee('Без доставки');
    }

    private function manager(): User
    {
        return User::query()->create([
            'name' => 'Manager',
            'email' => 'manager-view@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
        ]);
    }
}
