<?php

namespace Database\Seeders;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Application\ClientPortal\Command\RejectReviewCommand;
use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\CommandHandler\ApproveReviewHandler;
use App\Application\ClientPortal\CommandHandler\RejectReviewHandler;
use App\Application\ClientPortal\CommandHandler\SubmitReviewHandler;
use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CancelOrderCommand;
use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\MarkOrderWaitingForPartsCommand;
use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CancelOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderWaitingForPartsHandler;
use App\Application\OrderFulfillment\CommandHandler\SetWorkPricesHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Filament\Support\LeadToOrderFormData;
use App\Filament\Support\OrderFormCommandBuilder;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Prod-demo: воронка заказов, отзывы, POS для двух мастеров.
 *
 * Маркер DEMO: в internal_notes — идемпотентность при повторном db:seed.
 */
final class DemoOrderSeeder extends Seeder
{
    public const DEMO_MARKER = 'DEMO:';

    public function run(): void
    {
        if ($this->alreadySeeded()) {
            return;
        }

        $master = $this->requireMaster(IdentitySeeder::MASTER_EMAIL);
        $master2 = $this->requireMaster(IdentitySeeder::MASTER_2_EMAIL);

        $demoClient = $this->requireClient(ClientPortalSeeder::DEMO_CLIENT_PHONE);
        $client2 = $this->requireClient(ClientPortalSeeder::DEMO_CLIENT_2_PHONE);
        $client3 = $this->requireClient(ClientPortalSeeder::DEMO_CLIENT_3_PHONE);

        $equipment = EquipmentModel::query()
            ->where('name', EquipmentSeeder::STRONG_2100_NAME)
            ->firstOrFail();

        $warehouseItem = WarehouseItemModel::query()
            ->where('sku', 'DEMO-001')
            ->firstOrFail();

        $this->seedNewOrders($master);
        $this->seedCancelledOrder($master);
        $this->seedActiveOrder($master);
        $this->seedDemoClientActiveOrder($master, $demoClient);
        $this->seedWaitingPartsOrder($master, $warehouseItem);
        $this->seedReadyOrder($master);
        $this->seedDeliveryOrder($master);
        $this->seedComboOrder($master);
        $this->seedIssuedOrderWithApprovedReview($master, $demoClient);
        $this->seedIssuedOrderWithPendingReview($master, $client2);
        $this->seedIssuedOrderWithRejectedReview($master, $client3);
        $this->seedRepairOrder($master, $equipment);
        $this->seedEquipmentHistoryOrder($master, $equipment);
        $this->seedConvertedLeadOrder($master);
        $this->seedGuestOrderForLinking($master);
        $this->seedSecondMasterOrders($master2);
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

    private function seedCancelledOrder(UserModel $master): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'cancelled',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Клиент отменил', 'phone' => '+79001110099']),
            tools: [new OrderTool(null, 'manicure', 1)],
        ));

        app(CancelOrderHandler::class)->handle(new CancelOrderCommand($orderId));
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

    private function seedDemoClientActiveOrder(UserModel $master, ClientModel $client): void
    {
        $snapshot = new ClientSnapshot([
            'full_name' => $client->full_name,
            'phone' => $client->phone,
        ]);

        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'client-active',
            serviceTypes: ['sharpening'],
            snapshot: $snapshot,
            clientId: $client->id,
            tools: [new OrderTool(null, 'manicure', 4)],
        ));

        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Заточка маникюрного набора');
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

    private function seedDeliveryOrder(UserModel $master): void
    {
        $this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'delivery',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Алина Курьерова', 'phone' => '+79001110007']),
            needsDelivery: true,
            deliveryAddress: 'г. Томск, ул. Мокрушина, 9, кв. 12',
            tools: [new OrderTool(null, 'manicure', 6)],
        );
    }

    private function seedComboOrder(UserModel $master): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'combo',
            serviceTypes: ['sharpening', 'repair'],
            snapshot: new ClientSnapshot(['full_name' => 'Екатерина Комбо', 'phone' => '+79001110008']),
            problemDescription: 'Заточка + ремонт аппарата в одном заказе',
            tools: [new OrderTool(null, 'barber', 2)],
        ));

        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Заточка ножниц');
        $this->addWork($orderId, $master->id, 'Диагностика аппарата');
    }

    private function seedIssuedOrderWithApprovedReview(UserModel $master, ClientModel $client): void
    {
        $orderId = $this->issueClientOrder(
            master: $master,
            client: $client,
            marker: self::DEMO_MARKER.'issued-approved',
            workDescription: 'Заточка маникюрного инструмента',
            price: '600.00',
        );

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

    private function seedIssuedOrderWithPendingReview(UserModel $master, ClientModel $client): void
    {
        $this->issueClientOrder(
            master: $master,
            client: $client,
            marker: self::DEMO_MARKER.'issued-pending-review',
            workDescription: 'Заточка парикмахерских ножниц',
            price: '800.00',
            submitReview: true,
        );
    }

    private function seedIssuedOrderWithRejectedReview(UserModel $master, ClientModel $client): void
    {
        $orderId = $this->issueClientOrder(
            master: $master,
            client: $client,
            marker: self::DEMO_MARKER.'issued-rejected-review',
            workDescription: 'Заточка топора',
            price: '350.00',
        );

        $review = app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
            clientId: $client->id,
            orderId: $orderId,
            rating: 2,
            comment: 'Долго ждала, но качество нормальное.',
        ));

        $reviewId = $review->id();

        if ($reviewId !== null) {
            app(RejectReviewHandler::class)->handle(new RejectReviewCommand($reviewId));
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

    private function seedEquipmentHistoryOrder(UserModel $master, EquipmentModel $equipment): void
    {
        $orderId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'equipment-history',
            serviceTypes: ['repair'],
            snapshot: new ClientSnapshot(['full_name' => 'История SN', 'phone' => '+79001110010']),
            problemDescription: 'Прошлый ремонт подшипника',
        ));

        app(LinkEquipmentToOrderHandler::class)->handle(new LinkEquipmentToOrderCommand(
            orderId: $orderId,
            equipmentId: $equipment->id,
        ));
        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, 'Замена подшипника 608ZZ');
        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $orderId,
            pricesBySortOrder: [0 => '1200.00'],
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($orderId));
    }

    private function seedConvertedLeadOrder(UserModel $master): void
    {
        $lead = SiteLeadModel::query()
            ->where('phone', ClientPortalSeeder::DEMO_LEAD_PHONE)
            ->first();

        if ($lead === null || $lead->converted) {
            return;
        }

        $manager = UserModel::query()
            ->where('email', IdentitySeeder::MANAGER_EMAIL)
            ->first();

        if ($manager === null) {
            return;
        }

        $orderId = $this->requireOrderId(app(CreateOrderHandler::class)->handle(
            OrderFormCommandBuilder::buildCommand(
                LeadToOrderFormData::fromLead($lead, $manager->id)
            )
        ));

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

    private function seedSecondMasterOrders(UserModel $master): void
    {
        $this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'master2-new',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Заказ Ивана', 'phone' => '+79002220001']),
            tools: [new OrderTool(null, 'manicure', 2)],
        );

        $activeId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'master2-active',
            serviceTypes: ['repair'],
            snapshot: new ClientSnapshot(['full_name' => 'Заказ Ивана 2', 'phone' => '+79002220002']),
            problemDescription: 'Проверка педальки',
        ));

        $this->takeToWork($activeId, $master->id);
        $this->addWork($activeId, $master->id, 'Диагностика педали');

        $readyId = $this->requireOrderId($this->createAssignedOrder(
            masterId: $master->id,
            marker: self::DEMO_MARKER.'master2-ready',
            serviceTypes: ['sharpening'],
            snapshot: new ClientSnapshot(['full_name' => 'Заказ Ивана 3', 'phone' => '+79002220003']),
            tools: [new OrderTool(null, 'barber', 1)],
        ));

        $this->takeToWork($readyId, $master->id);
        $this->addWork($readyId, $master->id, 'Заточка филировочных ножниц');
        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $readyId,
            pricesBySortOrder: [0 => '400.00'],
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $readyId,
            masterId: $master->id,
        ));
    }

    private function issueClientOrder(
        UserModel $master,
        ClientModel $client,
        string $marker,
        string $workDescription,
        string $price,
        bool $submitReview = false,
    ): int {
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
        $this->markDemo($orderId, $master->id, $marker);
        $this->takeToWork($orderId, $master->id);
        $this->addWork($orderId, $master->id, $workDescription);
        app(SetWorkPricesHandler::class)->handle(new SetWorkPricesCommand(
            orderId: $orderId,
            pricesBySortOrder: [0 => $price],
        ));
        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $master->id,
        ));
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($orderId));

        if ($submitReview) {
            app(SubmitReviewHandler::class)->handle(new SubmitReviewCommand(
                clientId: $client->id,
                orderId: $orderId,
                rating: 4,
                comment: 'Хорошо, но хотелось бы быстрее.',
            ));
        }

        return $orderId;
    }

    /**
     * @param  list<string>  $serviceTypes
     * @param  list<OrderTool>  $tools
     */
    private function createAssignedOrder(
        int $masterId,
        string $marker,
        array $serviceTypes,
        ClientSnapshot $snapshot,
        ?OrderUrgency $urgency = null,
        array $tools = [],
        ?string $problemDescription = null,
        ?int $clientId = null,
        bool $needsDelivery = false,
        ?string $deliveryAddress = null,
    ): Order {
        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: $serviceTypes,
            clientId: $clientId,
            clientSnapshot: $snapshot,
            urgency: $urgency,
            needsDelivery: $needsDelivery,
            deliveryAddress: $deliveryAddress,
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
        OrderModel::query()->whereKey($orderId)->update(['internal_notes' => $marker]);
    }

    private function requireMaster(string $email): UserModel
    {
        return UserModel::query()->where('email', $email)->firstOrFail();
    }

    private function requireClient(string $phone): ClientModel
    {
        return ClientModel::query()->where('phone', $phone)->firstOrFail();
    }

    private function requireOrderId(Order $order): int
    {
        $orderId = $order->id();

        if ($orderId === null) {
            throw new RuntimeException('Заказ не получил id после сохранения.');
        }

        return $orderId;
    }
}
