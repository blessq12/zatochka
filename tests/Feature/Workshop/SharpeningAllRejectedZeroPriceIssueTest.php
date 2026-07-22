<?php

namespace Tests\Feature\Workshop;

use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\MarkOrderReadyCommand;
use App\Application\Order\Command\MarkOrderReadyHandler;
use App\Application\Order\Command\RejectOrderItemUnitsCommand;
use App\Application\Order\Command\RejectOrderItemUnitsHandler;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class SharpeningAllRejectedZeroPriceIssueTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_all_rejected_finish_ready_issue_without_payment(): void
    {
        $master = $this->createMaster('sharpening-all-rejected@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->startWork($flow['taskId']);

        app(RejectOrderItemUnitsHandler::class)->handle(new RejectOrderItemUnitsCommand(
            $flow['orderId'],
            $flow['orderItemId'],
            1,
            'неремонтопригодно',
        ));

        $item = OrderItemModel::query()->whereKey($flow['orderItemId'])->firstOrFail();
        $this->assertSame('rejected', $item->status);
        $this->assertSame(1, (int) $item->rejected_quantity);

        $this->finishTask($flow['taskId']);
        $this->assertTaskStatus($flow['taskId'], ProductionStatus::Completed);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::WorksCompleted);

        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($flow['orderId']));
        $this->assertOrderStatus($flow['orderId'], OrderStatus::Ready);

        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'cash'));
        $this->assertOrderStatus($flow['orderId'], OrderStatus::Issued);
        $this->assertSame(0, PaymentModel::query()->where('order_id', $flow['orderId'])->count());
    }
}
