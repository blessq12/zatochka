<?php

namespace Tests\Feature\Order;

use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Domain\Order\Event\OrderCancelled;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\VO\ProductionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class CancelOrderMidChainRollbackTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_listener_failure_rolls_back_order_and_workshop(): void
    {
        $master = $this->createMaster();
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->assertOrderStatus($flow['orderId'], OrderStatus::MasterAssigned);
        $this->assertTaskStatus($flow['taskId'], ProductionStatus::MasterAssigned);

        Event::listen(OrderCancelled::class, static function (): void {
            throw new RuntimeException('mid-chain fail');
        });

        try {
            app(CancelOrderHandler::class)->handle(new CancelOrderCommand(
                $flow['orderId'],
                'client cancelled',
            ));
            $this->fail('Expected mid-chain failure');
        } catch (RuntimeException $e) {
            $this->assertSame('mid-chain fail', $e->getMessage());
        }

        $this->assertOrderStatus($flow['orderId'], OrderStatus::MasterAssigned);
        $this->assertTaskStatus($flow['taskId'], ProductionStatus::MasterAssigned);
    }
}
