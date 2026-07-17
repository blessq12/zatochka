<?php

namespace App\Providers;

use App\Application\CRM\ReadPort\ClientReadPort;
use App\Application\Delivery\ReadPort\DeliveryReadPort;
use App\Application\Equipment\ReadPort\EquipmentReadPort;
use App\Application\Feedback\Port\CompletedOrderPort;
use App\Application\Feedback\ReadPort\ReviewReadPort;
use App\Application\Identity\Port\PasswordHasher;
use App\Application\Identity\ReadPort\StaffUserReadPort;
use App\Application\Finance\ReadPort\PaymentReadPort;
use App\Application\Inventory\ReadPort\StockReadPort;
use App\Application\Order\Port\MasterDirectoryPort;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Application\Order\ReadPort\OrderReadPort;
use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Application\Pricing\Port\PerformedWorkRefPort;
use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Application\Workshop\Port\EquipmentComponentBelongingPort;
use App\Application\Workshop\Port\OrderProductionContextPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Domain\CRM\Repository\ClientRepository;
use App\Domain\Identity\Repository\StaffUserRepository;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Domain\Finance\Repository\CashOperationRepository;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Order\Event\OrderCancelled;
use App\Domain\Order\Event\OrderMasterAssigned;
use App\Domain\Order\Event\OrderReturnedToMaster;
use App\Domain\Order\Event\ReceptionCompleted;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Domain\Workshop\Event\WorkStarted;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Infrastructure\CRM\ReadModel\EloquentClientReadModel;
use App\Infrastructure\CRM\Repository\EloquentClientRepository;
use App\Infrastructure\Delivery\ReadModel\EloquentDeliveryReadModel;
use App\Infrastructure\Delivery\Repository\EloquentDeliveryRequestRepository;
use App\Infrastructure\Equipment\ReadModel\EloquentEquipmentReadModel;
use App\Infrastructure\Equipment\Repository\EloquentClientEquipmentRepository;
use App\Infrastructure\Feedback\Port\EloquentCompletedOrderPort;
use App\Infrastructure\Feedback\ReadModel\EloquentReviewReadModel;
use App\Infrastructure\Feedback\Repository\EloquentReviewRepository;
use App\Infrastructure\Identity\ReadModel\EloquentStaffUserReadModel;
use App\Infrastructure\Identity\Repository\EloquentStaffUserRepository;
use App\Infrastructure\Identity\Service\LaravelPasswordHasher;
use App\Infrastructure\Finance\ReadModel\EloquentPaymentReadModel;
use App\Infrastructure\Finance\Repository\EloquentCashOperationRepository;
use App\Infrastructure\Finance\Repository\EloquentPaymentRepository;
use App\Infrastructure\Inventory\ReadModel\EloquentStockReadModel;
use App\Infrastructure\Inventory\Repository\EloquentStockItemRepository;
use App\Infrastructure\Order\Listener\MarkOrderInProgressOnWorkStarted;
use App\Infrastructure\Order\Listener\MarkOrderWorksCompletedOnProductionCompleted;
use App\Infrastructure\Order\Port\EloquentMasterDirectoryPort;
use App\Infrastructure\Order\ReadModel\EloquentOrderContainerReadModel;
use App\Infrastructure\Order\ReadModel\EloquentOrderReadModel;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use App\Infrastructure\Pricing\Listener\ClearWorkPricesOnOrderCancelled;
use App\Infrastructure\Pricing\Listener\ClearWorkPricesOnOrderReturnedToMaster;
use App\Infrastructure\Pricing\Port\EloquentOrderPricingGatePort;
use App\Infrastructure\Pricing\Port\EloquentPerformedWorkRefPort;
use App\Infrastructure\Pricing\ReadModel\EloquentWorkPriceReadModel;
use App\Infrastructure\Pricing\Repository\EloquentWorkPriceRepository;
use App\Infrastructure\Shared\Event\LaravelDomainEventPublisher;
use App\Infrastructure\Shared\Persistence\EloquentUnitOfWork;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Infrastructure\Workshop\Listener\CancelProductionTaskOnOrderCancelled;
use App\Infrastructure\Workshop\Listener\OpenAndAssignTasksOnOrderMasterAssigned;
use App\Infrastructure\Workshop\Listener\OpenProductionTasksOnReceptionCompleted;
use App\Infrastructure\Workshop\Listener\ReopenProductionTaskOnOrderReturnedToMaster;
use App\Infrastructure\Workshop\Port\EloquentEquipmentComponentBelongingPort;
use App\Infrastructure\Workshop\Port\EloquentOrderProductionContextPort;
use App\Infrastructure\Workshop\ReadModel\EloquentProductionTaskReadModel;
use App\Infrastructure\Workshop\Repository\EloquentProductionTaskRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainEventPublisher::class, LaravelDomainEventPublisher::class);
        $this->app->bind(EntityIdGenerator::class, SequentialEntityIdGenerator::class);
        $this->app->bind(UnitOfWork::class, EloquentUnitOfWork::class);

        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(ClientReadPort::class, EloquentClientReadModel::class);

        $this->app->bind(StaffUserRepository::class, EloquentStaffUserRepository::class);
        $this->app->bind(StaffUserReadPort::class, EloquentStaffUserReadModel::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);

        $this->app->bind(ClientEquipmentRepository::class, EloquentClientEquipmentRepository::class);
        $this->app->bind(EquipmentReadPort::class, EloquentEquipmentReadModel::class);

        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(OrderReadPort::class, EloquentOrderReadModel::class);
        $this->app->bind(OrderContainerReadPort::class, EloquentOrderContainerReadModel::class);
        $this->app->bind(MasterDirectoryPort::class, EloquentMasterDirectoryPort::class);

        $this->app->bind(ProductionTaskRepository::class, EloquentProductionTaskRepository::class);
        $this->app->bind(ProductionTaskReadPort::class, EloquentProductionTaskReadModel::class);
        $this->app->bind(OrderProductionContextPort::class, EloquentOrderProductionContextPort::class);
        $this->app->bind(EquipmentComponentBelongingPort::class, EloquentEquipmentComponentBelongingPort::class);

        $this->app->bind(WorkPriceRepository::class, EloquentWorkPriceRepository::class);
        $this->app->bind(WorkPriceReadPort::class, EloquentWorkPriceReadModel::class);
        $this->app->bind(PerformedWorkRefPort::class, EloquentPerformedWorkRefPort::class);
        $this->app->bind(OrderPricingGatePort::class, EloquentOrderPricingGatePort::class);

        $this->app->bind(StockItemRepository::class, EloquentStockItemRepository::class);
        $this->app->bind(StockReadPort::class, EloquentStockReadModel::class);

        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);
        $this->app->bind(CashOperationRepository::class, EloquentCashOperationRepository::class);
        $this->app->bind(PaymentReadPort::class, EloquentPaymentReadModel::class);

        $this->app->bind(DeliveryRequestRepository::class, EloquentDeliveryRequestRepository::class);
        $this->app->bind(DeliveryReadPort::class, EloquentDeliveryReadModel::class);

        $this->app->bind(ReviewRepository::class, EloquentReviewRepository::class);
        $this->app->bind(ReviewReadPort::class, EloquentReviewReadModel::class);
        $this->app->bind(CompletedOrderPort::class, EloquentCompletedOrderPort::class);
    }

    public function boot(): void
    {
        Event::listen(ReceptionCompleted::class, OpenProductionTasksOnReceptionCompleted::class);
        Event::listen(OrderMasterAssigned::class, OpenAndAssignTasksOnOrderMasterAssigned::class);
        Event::listen(OrderCancelled::class, CancelProductionTaskOnOrderCancelled::class);
        Event::listen(OrderCancelled::class, ClearWorkPricesOnOrderCancelled::class);
        Event::listen(OrderReturnedToMaster::class, ReopenProductionTaskOnOrderReturnedToMaster::class);
        Event::listen(OrderReturnedToMaster::class, ClearWorkPricesOnOrderReturnedToMaster::class);
        Event::listen(WorkStarted::class, MarkOrderInProgressOnWorkStarted::class);
        Event::listen(ProductionCompleted::class, MarkOrderWorksCompletedOnProductionCompleted::class);
    }
}
