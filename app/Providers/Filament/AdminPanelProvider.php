<?php

namespace App\Providers\Filament;

use App\Filament\Resources\BonusAccountResource;
use App\Filament\Resources\BonusSettingsResource;
use App\Filament\Resources\BonusTransactionResource;
use App\Filament\Resources\BranchResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\DiscountRuleResource;
use App\Filament\Resources\EquipmentTypeResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\RepairResource;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\StockCategoryResource;
use App\Filament\Resources\StockItemResource;
use App\Filament\Resources\StockMovementResource;
use App\Filament\Resources\TelegramChatResource;
use App\Filament\Resources\TelegramMessageResource;
use App\Filament\Resources\ToolResource;
use App\Filament\Resources\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cp')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->resources([
                // Заказы
                OrderResource::class,
                RepairResource::class,
                // Клиенты
                ClientResource::class,
                ReviewResource::class,
                NotificationResource::class,
                // Склад
                StockItemResource::class,
                StockCategoryResource::class,
                StockMovementResource::class,
                ToolResource::class,
                EquipmentTypeResource::class,
                // Организация
                CompanyResource::class,
                BranchResource::class,
                UserResource::class,
                // Бонусы
                BonusAccountResource::class,
                BonusTransactionResource::class,
                BonusSettingsResource::class,
                // Скидки
                DiscountRuleResource::class,
                // Telegram
                TelegramChatResource::class,
                TelegramMessageResource::class,
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
            ]);
    }
}
