<?php

namespace App\Filament\Support;

use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CancelOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\RecalculateOrderPriceCommand;
use App\Application\OrderFulfillment\Command\RemoveMaterialFromOrderCommand;
use App\Application\OrderFulfillment\Command\ReturnOrderForReworkCommand;
use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CancelOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\RecalculateOrderPriceHandler;
use App\Application\OrderFulfillment\CommandHandler\RemoveMaterialFromOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\ReturnOrderForReworkHandler;
use App\Application\OrderFulfillment\CommandHandler\SetWorkPricesHandler;
use App\Domain\OrderFulfillment\Entity\Order;

final class OrderManageActionSupport
{
    public static function assignMaster(int $orderId, int $masterId): void
    {
        app(AssignMasterToOrderHandler::class)->handle(
            new AssignMasterToOrderCommand($orderId, $masterId)
        );
    }

    /**
     * @param  array<int, string|null>  $pricesBySortOrder
     * @param  array<string, string|null>  $pricesByToolType
     */
    public static function setWorkPrices(
        int $orderId,
        array $pricesBySortOrder = [],
        array $pricesByToolType = [],
    ): Order {
        if ($pricesBySortOrder !== [] || $pricesByToolType !== []) {
            app(SetWorkPricesHandler::class)->handle(
                new SetWorkPricesCommand(
                    orderId: $orderId,
                    pricesBySortOrder: $pricesBySortOrder,
                    pricesByToolType: $pricesByToolType,
                )
            );
        }

        return self::recalculatePrice($orderId);
    }

    public static function addMaterial(int $orderId, int $warehouseItemId, string $quantity): Order
    {
        app(AddMaterialToOrderHandler::class)->handle(new AddMaterialToOrderCommand(
            orderId: $orderId,
            warehouseItemId: $warehouseItemId,
            quantity: $quantity,
        ));

        return self::recalculatePrice($orderId);
    }

    public static function removeMaterial(int $orderId, int $materialId): Order
    {
        app(RemoveMaterialFromOrderHandler::class)->handle(
            new RemoveMaterialFromOrderCommand($orderId, $materialId)
        );

        return self::recalculatePrice($orderId);
    }

    public static function recalculatePrice(int $orderId): Order
    {
        return app(RecalculateOrderPriceHandler::class)->handle(
            new RecalculateOrderPriceCommand($orderId)
        );
    }

    public static function linkEquipment(int $orderId, int $equipmentId): void
    {
        app(LinkEquipmentToOrderHandler::class)->handle(
            new LinkEquipmentToOrderCommand($orderId, $equipmentId)
        );
    }

    public static function issue(int $orderId): void
    {
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($orderId));
    }

    public static function returnForRework(int $orderId, int $managerId, string $feedback): Order
    {
        return app(ReturnOrderForReworkHandler::class)->handle(new ReturnOrderForReworkCommand(
            orderId: $orderId,
            managerId: $managerId,
            feedback: $feedback,
        ));
    }

    public static function cancel(int $orderId): void
    {
        app(CancelOrderHandler::class)->handle(new CancelOrderCommand($orderId));
    }

    public static function formatPrice(?string $price): string
    {
        if ($price === null || $price === '') {
            return '0 ₽';
        }

        return number_format((float) $price, 2, '.', ' ').' ₽';
    }
}
