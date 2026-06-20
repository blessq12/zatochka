<?php

namespace Database\Seeders;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\SubmitReviewHandler;
use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\MarkOrderWaitingForPartsCommand;
use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\Command\UpdateInternalNotesCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderWaitingForPartsHandler;
use App\Application\OrderFulfillment\CommandHandler\SetWorkPricesHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\UpdateInternalNotesHandler;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Database\Seeder;

final class DemoOrderSeeder extends Seeder
{
    private const DEMO_MARKER = 'DEMO:';

    public function run(): void
    {
        if ($this->alreadySeeded()) {
            return;
        }

        $master = UserModel::query()
            ->where('email', IdentitySeeder::MASTER_EMAIL)
            ->firstOrFail();

        $client = ClientModel::query()
            ->where('phone', ClientPortalSeeder::DEMO_CLIENT_PHONE)
            ->firstOrFail();

        $equipment = EquipmentModel::query()
            ->where('name', 'Аппарат Strong 2100')
            ->firstOrFail();

        $warehouseItem = WarehouseItemModel::query()
            ->where('sku', 'DEMO-001')
            ->firstOrFail();

        $this->seedNewOrders($master);
        $this->seedActiveOrder($master);
        $this->seedWaitingPartsOrder($master, $warehouseItem);
        $this->seedReadyOrder($master);
        $this->seedIssuedOrderWithReview($master, $client);
        $this->seedRepairOrder($master, $equipment);
        $this->seedConvertedLeadOrder($master);
        $this->seedGuestOrderForLinking($master);
    }

    private function alreadySeeded(): bool
    {
        return OrderModel::query()
            ->where('internal_notes', 'like', self::DEMO_MARKER.'%')
            ->exists();
    }

    private function seedNewOrders(UserModel $master): void
    {
        $this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'new-urgent',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Ольга Петрова', 'phone' => '+79001110001']),
            urgency: OrderUrgency::Urgent,
            tools: [new OrderTool(null, 'manicure', 3)],
        );

        $this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'new-standard',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Игорь Смирнов', 'phone' => '+79001110002']),
            tools: [new OrderTool(null, 'barber', 2)],
        );
    }

    private function seedActiveOrder(UserModel $master): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'active',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Мария Кузнецова', 'phone' => '+79001110003']),
            tools: [new OrderTool(null, 'manicure', 5)],
        ));

        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Заточка кусачек');
        $this->addWork($orderId, $master->id, 'Полировка рабочих поверхностей');
    }

    private function seedWaitingPartsOrder(UserModel $master, WarehouseItemModel $warehouseItem): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'waiting-parts',
            serviceTypes: ['repair'],
            snapshot: new ClientSnapshot(['full_name' => 'Дмитрий Орлов', 'phone' => '+79001110004']),
            problemDescription: 'Шум подшипника, нужна замена',
        ));

        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Диагностика аппарата');
        app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $orderId,
            warehouseItemId: $warehouseItem->id,
            quantity: '1',
        ));
        app(MarkOrderWaitingForPartsHandler::class)->handle(new MarkOrderWaitingForPartsCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
    }

    private function seedReadyOrder(UserModel $master): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'ready',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Светлана Белова', 'phone' => '+79001110005']),
            tools: [new OrderTool(null, 'groomer', 1)],
        ));

        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Заточка грумерских ножниц');
        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $orderId,
            pricesBySortOrder: [0 => '450.00'],
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
    }

    private function seedIssuedOrderWithReview(UserModel $master, ClientModel $client): void
    {
        $snapshot = new ClientSnapshot([
            'full_name' => $client->full_name,
            'phone' => $client->phone,
        ]);

        $orderId = $this->requireOrderId(app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: ['sharpening'],
            clientId: $client->id,
            clientSnapshot: $snapshot,
            tools: [new OrderTool(null, 'manicure', 2)],
        )));

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        $this->markDemo($orderId, $master->id, self::DEMO_MARKER.'issued');
        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Заточка маникюрного инструмента');
        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $orderId,
            pricesBySortOrder: [0 => '600.00'],
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($orderId));

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $client->id,
            orderId: $orderId,
            rating: 5,
            comment: 'Отличная работа, инструмент как новый!',
        ));

        $reviewId = $review->id();

        if ($reviewId !== null) {
            app(ApproveReviewHandler::class)->handle(new ApproveReviewCommand($reviewId));
        }
    }

    private function seedRepairOrder(UserModel $master, EquipmentModel $equipment): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'repair',
            serviceTypes: ['repair'],
            snapshot: new ClientSnapshot(['full_name' => 'Наталья Волкова', 'phone' => '+79001110006']),
            problemDescription: 'Не включается, подозрение на ремень',
        ));

        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand(
            orderId: $orderId,
            equipmentId: $equipment->id,
        ));
        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Диагностика и замена ремня');
    }

    private function seedConvertedLeadOrder(UserModel $master): void
    {
        $lead = SiteLeadModel::query()
            ->where('phone', ClientPortalSeeder::DEMO_LEAD_PHONE)
            ->first();

        if ($lead === null || $lead->converted) {
            return;
        }

        $orderId = $this->requireOrderId(app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: $lead->service_types,
            clientSnapshot: new ClientSnapshot(['full_name' => $lead->full_name, 'phone' => $lead->phone]),
            leadId: $lead->id,
            needsDelivery: $lead->needs_delivery,
            deliveryAddress: $lead->delivery_address,
            problemDescription: $lead->comment,
        )));

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        $this->markDemo($orderId, $master->id, self::DEMO_MARKER.'from-lead');
    }

    private function seedGuestOrderForLinking(UserModel $master): void
    {
        $this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'guest-link',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot([
                'full_name' => 'Гость для ЛК',
                'phone' => ClientPortalSeeder::DEMO_GUEST_PHONE,
            ]),
            tools: [new OrderTool(null, 'manicure', 1)],
        );
    }

    private function createAssignedOrder(
        int $masterId,
        string $marker,
        array $serviceTypes,
        ClientSnapshot $snapshot,
        ?OrderUrgency $urgency = null,
        array $tools = [],
        ?string $problemDescription = null,
    ): Order {
        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: $serviceTypes,
            clientSnapshot: $snapshot,
            urgency: $urgency,
            problemDescription: $problemDescription,
            tools: $tools,
        ));

        $orderId = $this->requireOrderId($order);

        app(AssignMasterToOrderHandler::class)->handle(new AssignMasterToOrderCommand(
            orderId: $orderId,
            masterId: $masterId,
        ));

        $this->markDemo($orderId, $masterId, $marker);

        return $order;
    }

    private function takeToWork(int $orderId, int $masterId): void
    {
        app(TakeOrderToWorkHandler::class)->handle(new TakeOrderToWorkCommand(
            orderId: $orderId,
            masterId: $masterId,
        ));
    }

    private function addWork(int $orderId, int $masterId, string $description): void
    {
        app(AddWorkHandler::class)->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $masterId,
            description: $description,
        ));
    }

    private function markDemo(int $orderId, int $masterId, string $marker): void
    {
        app(UpdateInternalNotesHandler::class)->handle(new UpdateInternalNotesCommand(
            orderId: $orderId,
            masterId: $masterId,
            notes: $marker,
        ));
    }

    private function requireOrderId(Order $order): int
    {
        $orderId = $order->id();

        if ($orderId === null) {
            throw new \RuntimeException('Заказ не получил id после сохранения.');
        }

        return $orderId;
    }
}
