<?php

namespace App\Providers;

use App\Application\CRM\ReadPort\ClientReadPort;
use App\Application\Delivery\ReadPort\DeliveryReadPort;
use App\Application\Equipment\ReadPort\EquipmentReadPort;
use App\Application\Finance\ReadPort\PaymentReadPort;
use App\Application\Identity\ReadPort\EmployeeReadPort;
use App\Application\Inventory\ReadPort\StockReadPort;
use App\Application\Order\ReadPort\OrderReadPort;
use App\Application\Pricing\ReadPort\EstimateReadPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Domain\CRM\Repository\ClientRepository;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Finance\Repository\CashOperationRepository;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Identity\Repository\EmployeeRepository;
use App\Domain\Identity\Repository\RoleRepository;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Order\Event\ReceptionCompleted;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Infrastructure\CRM\ReadModel\EloquentClientReadModel;
use App\Infrastructure\CRM\Repository\EloquentClientRepository;
use App\Infrastructure\Delivery\ReadModel\EloquentDeliveryReadModel;
use App\Infrastructure\Delivery\Repository\EloquentDeliveryRequestRepository;
use App\Infrastructure\Equipment\ReadModel\EloquentEquipmentReadModel;
use App\Infrastructure\Equipment\Repository\EloquentClientEquipmentRepository;
use App\Infrastructure\Finance\ReadModel\EloquentPaymentReadModel;
use App\Infrastructure\Finance\Repository\EloquentCashOperationRepository;
use App\Infrastructure\Finance\Repository\EloquentPaymentRepository;
use App\Infrastructure\Identity\ReadModel\EloquentEmployeeReadModel;
use App\Infrastructure\Identity\Repository\EloquentEmployeeRepository;
use App\Infrastructure\Identity\Repository\EloquentRoleRepository;
use App\Infrastructure\Inventory\ReadModel\EloquentStockReadModel;
use App\Infrastructure\Inventory\Repository\EloquentStockItemRepository;
use App\Infrastructure\Order\ReadModel\EloquentOrderReadModel;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use App\Infrastructure\Pricing\Listener\CreateEstimateOnProductionCompleted;
use App\Infrastructure\Pricing\ReadModel\EloquentEstimateReadModel;
use App\Infrastructure\Pricing\Repository\EloquentEstimateRepository;
use App\Infrastructure\Shared\Event\LaravelDomainEventPublisher;
use App\Infrastructure\Workshop\Listener\OpenProductionTasksOnReceptionCompleted;
use App\Infrastructure\Workshop\ReadModel\EloquentProductionTaskReadModel;
use App\Infrastructure\Workshop\Repository\EloquentProductionTaskRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainEventPublisher::class, LaravelDomainEventPublisher::class);

        $this->app->bind(ClientRepository::class, EloquentClientRepository::class);
        $this->app->bind(ClientReadPort::class, EloquentClientReadModel::class);

        $this->app->bind(EmployeeRepository::class, EloquentEmployeeRepository::class);
        $this->app->bind(RoleRepository::class, EloquentRoleRepository::class);
        $this->app->bind(EmployeeReadPort::class, EloquentEmployeeReadModel::class);

        $this->app->bind(ClientEquipmentRepository::class, EloquentClientEquipmentRepository::class);
        $this->app->bind(EquipmentReadPort::class, EloquentEquipmentReadModel::class);

        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(OrderReadPort::class, EloquentOrderReadModel::class);

        $this->app->bind(ProductionTaskRepository::class, EloquentProductionTaskRepository::class);
        $this->app->bind(ProductionTaskReadPort::class, EloquentProductionTaskReadModel::class);

        $this->app->bind(EstimateRepository::class, EloquentEstimateRepository::class);
        $this->app->bind(EstimateReadPort::class, EloquentEstimateReadModel::class);

        $this->app->bind(StockItemRepository::class, EloquentStockItemRepository::class);
        $this->app->bind(StockReadPort::class, EloquentStockReadModel::class);

        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);
        $this->app->bind(CashOperationRepository::class, EloquentCashOperationRepository::class);
        $this->app->bind(PaymentReadPort::class, EloquentPaymentReadModel::class);

        $this->app->bind(DeliveryRequestRepository::class, EloquentDeliveryRequestRepository::class);
        $this->app->bind(DeliveryReadPort::class, EloquentDeliveryReadModel::class);
    }

    public function boot(): void
    {
        Event::listen(ReceptionCompleted::class, OpenProductionTasksOnReceptionCompleted::class);
        Event::listen(ProductionCompleted::class, CreateEstimateOnProductionCompleted::class);
    }
}
