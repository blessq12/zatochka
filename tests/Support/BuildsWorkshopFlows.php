<?php

namespace Tests\Support;

use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Equipment\DTO\EquipmentPartDTO;
use App\Application\Order\Command\AssignOrderMasterCommand;
use App\Application\Order\Command\AssignOrderMasterHandler;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Workshop\Command\AddMasterWorkCommand;
use App\Application\Workshop\Command\AddMasterWorkHandler;
use App\Application\Workshop\Command\FinishProductionTaskCommand;
use App\Application\Workshop\Command\FinishProductionTaskHandler;
use App\Application\Workshop\Command\StartWorkCommand;
use App\Application\Workshop\Command\StartWorkHandler;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Equipment\Model\EquipmentComponentModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

trait BuildsWorkshopFlows
{
    protected function createMaster(string $email = 'master@test.local'): User
    {
        return User::query()->create([
            'name' => 'Test Master',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => UserRole::Master,
        ]);
    }

    protected function registerClient(string $name, string $phone): int
    {
        $ids = app(EntityIdGenerator::class);
        $clientId = $ids->next('client')->value;

        app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            $clientId,
            $ids->next('bonus_account')->value,
            $phone,
            $name,
        ));

        return $clientId;
    }

    /**
     * @param list<array{name: string, serialNumber?: ?string}> $parts
     */
    protected function registerEquipment(
        int $clientId,
        string $title,
        string $brand,
        string $modelName,
        array $parts = [],
    ): int {
        $ids = app(EntityIdGenerator::class);
        $equipmentId = $ids->next('equipment')->value;
        $partDtos = [];

        foreach ($parts as $part) {
            $partDtos[] = new EquipmentPartDTO(
                $ids->next('equipment_component')->value,
                $part['name'],
                $part['serialNumber'] ?? null,
            );
        }

        app(RegisterEquipmentHandler::class)->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $title,
            $brand,
            $modelName,
            $clientId,
            null,
            $partDtos,
        ));

        return $equipmentId;
    }

    /**
     * @return array{orderId: string, masterId: int, taskId: int, orderItemId: int}
     */
    protected function createSharpeningOrderWithMaster(User $master): array
    {
        $orderId = OrderId::generate()->value;
        $clientId = $this->registerClient('Smoke Client', '+79990001122');

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

        app(AssignOrderMasterHandler::class)->handle(new AssignOrderMasterCommand(
            $orderId,
            $master->id,
        ));

        $task = ProductionTaskModel::query()->where('order_id', $orderId)->firstOrFail();
        $item = OrderItemModel::query()->where('order_id', $orderId)->firstOrFail();

        return [
            'orderId' => $orderId,
            'masterId' => (int) $master->id,
            'taskId' => (int) $task->id,
            'orderItemId' => (int) $item->id,
        ];
    }

    /**
     * @return array{orderId: string, masterId: int, taskId: int, orderItemId: int, componentId: int}
     */
    protected function createRepairOrderWithMaster(User $master): array
    {
        $orderId = OrderId::generate()->value;
        $clientId = $this->registerClient('Repair Client', '+79990003344');
        $equipmentId = $this->registerEquipment(
            $clientId,
            'Газонокосилка',
            'Honda',
            'HRX',
            [['name' => 'Нож', 'serialNumber' => 'SN-1']],
        );

        app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            orderId: $orderId,
            clientId: $clientId,
            estimatedAmount: '2500.00',
            items: [
                new CreateOrderItemDTO(
                    clientEquipmentId: $equipmentId,
                ),
            ],
            serviceType: 'repair',
            billingType: 'paid',
            urgency: 'normal',
        ));

        app(AssignOrderMasterHandler::class)->handle(new AssignOrderMasterCommand(
            $orderId,
            $master->id,
        ));

        $task = ProductionTaskModel::query()->where('order_id', $orderId)->firstOrFail();
        $item = OrderItemModel::query()->where('order_id', $orderId)->firstOrFail();
        $component = EquipmentComponentModel::query()
            ->where('equipment_id', $item->client_equipment_id)
            ->firstOrFail();

        return [
            'orderId' => $orderId,
            'masterId' => (int) $master->id,
            'taskId' => (int) $task->id,
            'orderItemId' => (int) $item->id,
            'componentId' => (int) $component->id,
        ];
    }

    protected function startWork(int $taskId): void
    {
        $workExecutionId = app(EntityIdGenerator::class)->next('work_execution')->value;

        app(StartWorkHandler::class)->handle(new StartWorkCommand(
            $taskId,
            $workExecutionId,
            'started',
        ));
    }

    protected function addSharpeningWork(int $taskId, int $masterId, int $orderItemId): void
    {
        $workId = app(EntityIdGenerator::class)->next('performed_work')->value;

        app(AddMasterWorkHandler::class)->handle(new AddMasterWorkCommand(
            $taskId,
            $workId,
            $masterId,
            'заточка выполнена',
            $orderItemId,
        ));
    }

    protected function addRepairWork(int $taskId, int $masterId, int $componentId): void
    {
        $workId = app(EntityIdGenerator::class)->next('performed_work')->value;

        app(AddMasterWorkHandler::class)->handle(new AddMasterWorkCommand(
            $taskId,
            $workId,
            $masterId,
            'ремонт компонента',
            null,
            $componentId,
        ));
    }

    protected function finishTask(int $taskId): void
    {
        app(FinishProductionTaskHandler::class)->handle(new FinishProductionTaskCommand($taskId));
    }

    protected function assertOrderStatus(string $orderId, OrderStatus $status): void
    {
        $this->assertSame(
            $status->value,
            OrderModel::query()->whereKey($orderId)->value('status'),
        );
    }

    protected function assertTaskStatus(int $taskId, ProductionStatus $status): void
    {
        $this->assertSame(
            $status->value,
            ProductionTaskModel::query()->whereKey($taskId)->value('status'),
        );
    }
}
