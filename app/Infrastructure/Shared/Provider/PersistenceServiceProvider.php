<?php

namespace App\Infrastructure\Shared\Provider;

use App\Domain\Catalog\Repository\BranchRepositoryInterface;
use App\Domain\Catalog\Repository\PriceBlockRepositoryInterface;
use App\Domain\Catalog\Repository\PriceItemRepositoryInterface;
use App\Domain\Catalog\Repository\SiteSettingRepositoryInterface;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;
use App\Domain\ClientPortal\Repository\SiteLeadRepositoryInterface;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Domain\Warehouse\Repository\StockMovementRepositoryInterface;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;
use App\Infrastructure\Catalog\Persistence\Repository\EloquentBranchRepository;
use App\Infrastructure\Catalog\Persistence\Repository\EloquentPriceBlockRepository;
use App\Infrastructure\Catalog\Persistence\Repository\EloquentPriceItemRepository;
use App\Infrastructure\Catalog\Persistence\Repository\EloquentSiteSettingRepository;
use App\Infrastructure\ClientPortal\Persistence\Repository\EloquentClientRepository;
use App\Infrastructure\ClientPortal\Persistence\Repository\EloquentReviewRepository;
use App\Infrastructure\ClientPortal\Persistence\Repository\EloquentSiteLeadRepository;
use App\Infrastructure\Equipment\Persistence\Repository\EloquentEquipmentRepository;
use App\Infrastructure\Identity\Persistence\Repository\EloquentMasterRepository;
use App\Infrastructure\OrderFulfillment\Persistence\Repository\EloquentOrderRepository;
use App\Infrastructure\Warehouse\Persistence\Repository\EloquentStockMovementRepository;
use App\Infrastructure\Warehouse\Persistence\Repository\EloquentWarehouseItemRepository;
use Illuminate\Support\ServiceProvider;

final class PersistenceServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        OrderRepositoryInterface::class => EloquentOrderRepository::class,
        ClientRepositoryInterface::class => EloquentClientRepository::class,
        SiteLeadRepositoryInterface::class => EloquentSiteLeadRepository::class,
        ReviewRepositoryInterface::class => EloquentReviewRepository::class,
        BranchRepositoryInterface::class => EloquentBranchRepository::class,
        PriceBlockRepositoryInterface::class => EloquentPriceBlockRepository::class,
        PriceItemRepositoryInterface::class => EloquentPriceItemRepository::class,
        SiteSettingRepositoryInterface::class => EloquentSiteSettingRepository::class,
        EquipmentRepositoryInterface::class => EloquentEquipmentRepository::class,
        WarehouseItemRepositoryInterface::class => EloquentWarehouseItemRepository::class,
        StockMovementRepositoryInterface::class => EloquentStockMovementRepository::class,
        MasterRepositoryInterface::class => EloquentMasterRepository::class,
    ];
}
