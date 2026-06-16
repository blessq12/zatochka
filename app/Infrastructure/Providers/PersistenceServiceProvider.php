<?php

namespace App\Infrastructure\Providers;

use App\Domain\Catalog\Repositories\BranchRepositoryInterface;
use App\Domain\Catalog\Repositories\PriceBlockRepositoryInterface;
use App\Domain\Catalog\Repositories\PriceItemRepositoryInterface;
use App\Domain\Catalog\Repositories\SiteSettingRepositoryInterface;
use App\Domain\ClientPortal\Repositories\ClientRepositoryInterface;
use App\Domain\ClientPortal\Repositories\ReviewRepositoryInterface;
use App\Domain\ClientPortal\Repositories\SiteLeadRepositoryInterface;
use App\Domain\Equipment\Repositories\EquipmentRepositoryInterface;
use App\Domain\Identity\Repositories\MasterRepositoryInterface;
use App\Domain\OrderFulfillment\Repositories\OrderRepositoryInterface;
use App\Domain\Warehouse\Repositories\StockMovementRepositoryInterface;
use App\Domain\Warehouse\Repositories\WarehouseItemRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\Catalog\EloquentBranchRepository;
use App\Infrastructure\Persistence\Repositories\Catalog\EloquentPriceBlockRepository;
use App\Infrastructure\Persistence\Repositories\Catalog\EloquentPriceItemRepository;
use App\Infrastructure\Persistence\Repositories\Catalog\EloquentSiteSettingRepository;
use App\Infrastructure\Persistence\Repositories\ClientPortal\EloquentClientRepository;
use App\Infrastructure\Persistence\Repositories\ClientPortal\EloquentReviewRepository;
use App\Infrastructure\Persistence\Repositories\ClientPortal\EloquentSiteLeadRepository;
use App\Infrastructure\Persistence\Repositories\Equipment\EloquentEquipmentRepository;
use App\Infrastructure\Persistence\Repositories\Identity\EloquentMasterRepository;
use App\Infrastructure\Persistence\Repositories\OrderFulfillment\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\Warehouse\EloquentStockMovementRepository;
use App\Infrastructure\Persistence\Repositories\Warehouse\EloquentWarehouseItemRepository;
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
