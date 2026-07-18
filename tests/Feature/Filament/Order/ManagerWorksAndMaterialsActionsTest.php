<?php

namespace Tests\Feature\Filament\Order;

use App\Application\Inventory\Command\OpenStockItemCommand;
use App\Application\Inventory\Command\OpenStockItemHandler;
use App\Application\Inventory\Command\SyncOrderMaterialWriteOffItem;
use App\Application\Inventory\Command\SyncOrderMaterialWriteOffsCommand;
use App\Application\Inventory\Command\SyncOrderMaterialWriteOffsHandler;
use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Workshop\Command\SyncOrderPerformedWorkItem;
use App\Application\Workshop\Command\SyncOrderPerformedWorksCommand;
use App\Application\Workshop\Command\SyncOrderPerformedWorksHandler;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class ManagerWorksAndMaterialsActionsTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_manager_work_and_material_actions_visible_only_on_works_completed(): void
    {
        $this->actingAs($this->manager());

        $master = $this->createMaster('mgr-actions@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);

        Livewire::test(ViewOrder::class, ['record' => $flow['orderId']])
            ->assertSuccessful()
            ->assertDontSee('Редактировать работы')
            ->assertDontSee('Редактировать материалы');

        $this->finishTask($flow['taskId']);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::WorksCompleted);

        Livewire::test(ViewOrder::class, ['record' => $flow['orderId']])
            ->assertSuccessful()
            ->assertSee('Редактировать работы')
            ->assertSee('Цены работ')
            ->assertSee('Редактировать материалы');
    }

    public function test_sync_order_performed_works_adds_updates_and_removes(): void
    {
        $master = $this->createMaster('mgr-works@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);
        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $existingId = (int) PerformedWorkModel::query()
            ->where('production_task_id', $flow['taskId'])
            ->value('id');

        app(SyncOrderPerformedWorksHandler::class)->handle(new SyncOrderPerformedWorksCommand(
            $flow['orderId'],
            [
                new SyncOrderPerformedWorkItem(
                    text: 'исправленный текст',
                    workId: $existingId,
                    orderItemId: $flow['orderItemId'],
                ),
                new SyncOrderPerformedWorkItem(
                    text: 'новая работа менеджера',
                    orderItemId: $flow['orderItemId'],
                ),
            ],
        ));

        $this->assertDatabaseHas('performed_works', [
            'id' => $existingId,
            'description' => 'исправленный текст',
        ]);
        $this->assertDatabaseHas('performed_works', [
            'description' => 'новая работа менеджера',
            'production_task_id' => $flow['taskId'],
        ]);
        $this->assertSame(2, PerformedWorkModel::query()->where('production_task_id', $flow['taskId'])->count());

        app(SyncOrderPerformedWorksHandler::class)->handle(new SyncOrderPerformedWorksCommand(
            $flow['orderId'],
            [
                new SyncOrderPerformedWorkItem(
                    text: 'исправленный текст',
                    workId: $existingId,
                    orderItemId: $flow['orderItemId'],
                ),
            ],
        ));

        $this->assertSame(1, PerformedWorkModel::query()->where('production_task_id', $flow['taskId'])->count());
        $this->assertDatabaseMissing('performed_works', [
            'description' => 'новая работа менеджера',
        ]);
    }

    public function test_sync_order_material_write_offs_adds_updates_and_removes(): void
    {
        $master = $this->createMaster('mgr-materials@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);
        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $ids = app(EntityIdGenerator::class);
        $stockItemId = $ids->next('stock_item')->value;
        app(OpenStockItemHandler::class)->handle(new OpenStockItemCommand(
            $stockItemId,
            $ids->next('material')->value,
            'Test Blade',
            UnitOfMeasure::Piece->value,
            'spare_part',
            '20',
            '100.00',
        ));

        app(SyncOrderMaterialWriteOffsHandler::class)->handle(new SyncOrderMaterialWriteOffsCommand(
            $flow['orderId'],
            [
                new SyncOrderMaterialWriteOffItem(
                    stockItemId: $stockItemId,
                    quantity: '2',
                    unitPrice: '100.00',
                    orderItemId: $flow['orderItemId'],
                ),
            ],
        ));

        $lines = app(OrderMaterialWriteOffReadPort::class)->listActiveByOrderId($flow['orderId']);
        $this->assertCount(1, $lines);
        $this->assertSame('2.000', number_format((float) $lines[0]->quantity, 3, '.', ''));

        $movementId = $lines[0]->movementId;

        app(SyncOrderMaterialWriteOffsHandler::class)->handle(new SyncOrderMaterialWriteOffsCommand(
            $flow['orderId'],
            [
                new SyncOrderMaterialWriteOffItem(
                    stockItemId: $stockItemId,
                    quantity: '3',
                    unitPrice: '120.00',
                    movementId: $movementId,
                    orderItemId: $flow['orderItemId'],
                ),
            ],
        ));

        $updated = app(OrderMaterialWriteOffReadPort::class)->listActiveByOrderId($flow['orderId']);
        $this->assertCount(1, $updated);
        $this->assertSame('120.00', $updated[0]->unitPrice);
        $this->assertSame('3.000', number_format((float) $updated[0]->quantity, 3, '.', ''));

        app(SyncOrderMaterialWriteOffsHandler::class)->handle(new SyncOrderMaterialWriteOffsCommand(
            $flow['orderId'],
            [],
        ));

        $this->assertSame([], app(OrderMaterialWriteOffReadPort::class)->listActiveByOrderId($flow['orderId']));
    }

    private function manager(): User
    {
        return User::query()->create([
            'name' => 'Manager',
            'email' => 'manager-works-materials@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
        ]);
    }
}
