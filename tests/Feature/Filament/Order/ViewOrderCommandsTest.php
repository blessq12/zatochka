<?php

namespace Tests\Feature\Filament\Order;

use App\Application\Equipment\Command\AddComponentCommand;
use App\Application\Equipment\Command\AddComponentHandler;
use App\Application\Equipment\Command\RegisterSerialNumberCommand;
use App\Application\Equipment\Command\RegisterSerialNumberHandler;
use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Application\Equipment\Command\UpdateEquipmentHandler;
use App\Application\Order\Command\CompleteReceptionCommand;
use App\Application\Order\Command\CompleteReceptionHandler;
use App\Application\Order\Command\CreatePublicOrderCommand;
use App\Application\Order\Command\CreatePublicOrderHandler;
use App\Application\Order\DTO\ReceptionItemDTO;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Equipment\Actions\EditWebsiteOrderEquipmentAction;
use App\Filament\Order\Resources\OrderResource\Actions\OrderMutationActions;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Equipment\Model\EquipmentComponentModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Order\Model\ReceptionDataModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class ViewOrderCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_reception_moves_order_to_reception_completed(): void
    {
        $orderId = $this->createWebsiteSharpeningOrder();
        $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');
        $receptionId = app(EntityIdGenerator::class)->next('reception')->value;

        app(CompleteReceptionHandler::class)->handle(new CompleteReceptionCommand(
            $orderId,
            [
                new ReceptionItemDTO(
                    $itemId,
                    $receptionId,
                    'Инструмент в хорошем состоянии',
                    'Без сколов',
                ),
            ],
        ));

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => OrderStatus::ReceptionCompleted->value,
        ]);
        $this->assertDatabaseHas('reception_data', [
            'id' => $receptionId,
            'order_item_id' => $itemId,
            'condition_description' => 'Инструмент в хорошем состоянии',
            'visual_notes' => 'Без сколов',
        ]);
        $this->assertSame(1, ReceptionDataModel::query()->where('order_item_id', $itemId)->count());
    }

    public function test_view_order_does_not_show_complete_reception_action(): void
    {
        $this->actingAs($this->manager());
        $orderId = $this->createWebsiteSharpeningOrder();

        Livewire::test(ViewOrder::class, ['record' => $orderId])
            ->assertSuccessful()
            ->assertDontSee('Завершить приёмку')
            ->assertSee('Назначить');
    }

    public function test_edit_website_order_equipment_updates_profile_and_adds_component(): void
    {
        $this->actingAs($this->manager());

        $result = app(CreatePublicOrderHandler::class)->handle(new CreatePublicOrderCommand(
            fullName: 'Ремонт Клиент',
            phone: '+7 (999) 555-66-77',
            serviceType: 'repair',
            needsDelivery: false,
            deliveryAddress: null,
            comment: null,
            intake: [
                'device_name' => 'Wahl Super',
                'equipment_type' => 'clipper',
                'problem_description' => 'Не включается',
                'urgency_type' => 'standard',
            ],
        ));

        $orderId = $result['order_id'];
        $equipmentId = (int) OrderItemModel::query()
            ->where('order_id', $orderId)
            ->value('client_equipment_id');

        $order = OrderModel::query()->with('items')->findOrFail($orderId);
        $this->assertTrue(EditWebsiteOrderEquipmentAction::isVisible($order));

        Livewire::test(ViewOrder::class, ['record' => $orderId])
            ->assertSuccessful();

        $ids = app(EntityIdGenerator::class);
        $clientId = (int) ClientEquipmentModel::query()->whereKey($equipmentId)->value('client_id');

        app(UnitOfWork::class)->execute(function () use ($equipmentId, $ids, $clientId): void {
            app(UpdateEquipmentHandler::class)->handle(new UpdateEquipmentCommand(
                $equipmentId,
                'Wahl Super Taper',
                'Wahl',
                'Super Taper',
                'clipper',
                $clientId,
            ));

            app(AddComponentHandler::class)->handle(new AddComponentCommand(
                $equipmentId,
                $ids->next('equipment_component')->value,
                'Нож',
                'SN-001',
            ));
        });

        $this->assertDatabaseHas('client_equipment', [
            'id' => $equipmentId,
            'title' => 'Wahl Super Taper',
            'brand' => 'Wahl',
            'model_name' => 'Super Taper',
            'equipment_type' => 'clipper',
        ]);
        $this->assertDatabaseHas('equipment_components', [
            'equipment_id' => $equipmentId,
            'name' => 'Нож',
            'serial_number' => 'SN-001',
        ]);
        $this->assertSame(1, EquipmentComponentModel::query()->where('equipment_id', $equipmentId)->count());
        $this->assertTrue(OrderMutationActions::websiteRepairEquipmentReadyForMaster(
            OrderModel::query()->with('items.equipment.components')->findOrFail($orderId),
        ));
    }

    public function test_assign_master_blocked_until_website_repair_equipment_has_serialized_part(): void
    {
        $result = app(CreatePublicOrderHandler::class)->handle(new CreatePublicOrderCommand(
            fullName: 'Ремонт Блок',
            phone: '+7 (999) 777-88-99',
            serviceType: 'repair',
            needsDelivery: false,
            deliveryAddress: null,
            comment: null,
            intake: [
                'device_name' => 'Wahl',
                'equipment_type' => 'clipper',
                'problem_description' => 'Не включается после падения',
                'urgency_type' => 'standard',
            ],
        ));

        $orderId = $result['order_id'];
        $order = OrderModel::query()->with('items.equipment.components')->findOrFail($orderId);

        $this->assertFalse(OrderMutationActions::websiteRepairEquipmentReadyForMaster($order));

        $equipmentId = (int) OrderItemModel::query()
            ->where('order_id', $orderId)
            ->value('client_equipment_id');

        app(AddComponentHandler::class)->handle(new AddComponentCommand(
            $equipmentId,
            app(EntityIdGenerator::class)->next('equipment_component')->value,
            'Нож',
            null,
        ));

        $this->assertFalse(OrderMutationActions::websiteRepairEquipmentReadyForMaster(
            OrderModel::query()->with('items.equipment.components')->findOrFail($orderId),
        ));

        $componentId = (int) EquipmentComponentModel::query()
            ->where('equipment_id', $equipmentId)
            ->value('id');

        app(RegisterSerialNumberHandler::class)->handle(
            new RegisterSerialNumberCommand(
                $equipmentId,
                $componentId,
                'SN-READY',
            ),
        );

        $this->assertTrue(OrderMutationActions::websiteRepairEquipmentReadyForMaster(
            OrderModel::query()->with('items.equipment.components')->findOrFail($orderId),
        ));
    }

    public function test_edit_website_equipment_action_hidden_for_admin_orders(): void
    {
        $this->actingAs($this->manager());

        $orderId = $this->createWebsiteSharpeningOrder();
        OrderModel::query()->whereKey($orderId)->update(['source' => 'admin']);

        $order = OrderModel::query()->whereKey($orderId)->firstOrFail();
        $this->assertFalse(EditWebsiteOrderEquipmentAction::isVisible($order));

        Livewire::test(ViewOrder::class, ['record' => $orderId])
            ->assertSuccessful();
    }

    private function createWebsiteSharpeningOrder(): string
    {
        $result = app(CreatePublicOrderHandler::class)->handle(new CreatePublicOrderCommand(
            fullName: 'Сайт Клиент',
            phone: '+7 (999) 111-22-33',
            serviceType: 'sharpening',
            needsDelivery: false,
            deliveryAddress: null,
            comment: 'нужна заточка',
            intake: [
                'tool_type' => 'kitchen_knife',
                'tools_count' => 1,
            ],
        ));

        return $result['order_id'];
    }

    private function manager(): User
    {
        return User::query()->create([
            'name' => 'Manager',
            'email' => 'manager-view-order-commands@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
        ]);
    }
}
