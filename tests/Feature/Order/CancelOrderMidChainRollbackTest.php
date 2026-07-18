<?php

namespace Tests\Feature\Order;

use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\Event\OrderCancelled;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class CancelOrderMidChainRollbackTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_listener_failure_rolls_back_created_order_cancel(): void
    {
        $orderId = OrderId::generate()->value;
        $clientId = $this->registerClient('Cancel Client', '+79990002200');

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

        $this->assertOrderStatus($orderId, OrderStatus::Created);

        Event::listen(OrderCancelled::class, static function (): void {
            throw new RuntimeException('mid-chain fail');
        });

        try {
            app(CancelOrderHandler::class)->handle(new CancelOrderCommand(
                $orderId,
                'client cancelled',
            ));
            $this->fail('Expected mid-chain failure');
        } catch (RuntimeException $e) {
            $this->assertSame('mid-chain fail', $e->getMessage());
        }

        $this->assertOrderStatus($orderId, OrderStatus::Created);
    }

    public function test_cancel_forbidden_after_master_assigned(): void
    {
        $master = $this->createMaster();
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->expectException(DomainException::class);

        app(CancelOrderHandler::class)->handle(new CancelOrderCommand(
            $flow['orderId'],
            'too late',
        ));
    }
}
