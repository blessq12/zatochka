<?php

namespace App\Providers\Filament;

use App\Filament\Resources\BranchResource;
use App\Filament\Resources\BonusSettingsResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\EquipmentResource;
use App\Filament\Resources\MasterResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PriceItemResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\WarehouseCategoryResource;
use App\Filament\Resources\WarehouseItemResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use App\Filament\Widgets\AnalyticsWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\MastersPerformanceWidget;
use App\Filament\Widgets\OldIssuedOrdersWidget;
use App\Filament\Widgets\OrdersOverviewWidget;
use App\Filament\Widgets\QuickActionsWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />'
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>'
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cp')
            ->login()
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                QuickActionsWidget::class,
                OrdersOverviewWidget::class,
                AnalyticsWidget::class,
                LowStockWidget::class,
                MastersPerformanceWidget::class,
                OldIssuedOrdersWidget::class,
            ])
            ->resources([
                // Заказы
                OrderResource::class,
                // Клиенты
                ClientResource::class,
                // Организация
                CompanyResource::class,
                BranchResource::class,
                UserResource::class,
                MasterResource::class,
                // Справочники
                PriceItemResource::class,
                EquipmentResource::class,
                // Настройки
                BonusSettingsResource::class,
                // Склад
                WarehouseCategoryResource::class,
                WarehouseItemResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
