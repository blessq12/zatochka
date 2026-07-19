<?php

namespace Tests\Feature\Workshop;

use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Order\Model\OrderModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class MasterPosOrderVisibilityTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_ready_and_issued_orders_are_hidden_from_master_pos(): void
    {
        $master = $this->createMaster('master-pos-visibility@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $readPort = app(ProductionTaskReadPort::class);

        $visible = $readPort->listForMasterFunnel($flow['masterId'], 'completed');
        $this->assertSame(1, $visible['meta']['total']);
        $this->assertSame(1, $readPort->countsForMaster($flow['masterId'])->completed);
        $this->assertNotNull($readPort->findCardById($flow['taskId']));

        OrderModel::query()->whereKey($flow['orderId'])->update([
            'status' => OrderStatus::Ready->value,
        ]);

        $ready = $readPort->listForMasterFunnel($flow['masterId'], 'completed');
        $this->assertSame(0, $ready['meta']['total']);
        $this->assertSame([], $ready['items']);
        $this->assertSame(0, $readPort->countsForMaster($flow['masterId'])->completed);
        $this->assertNull($readPort->findCardById($flow['taskId']));

        OrderModel::query()->whereKey($flow['orderId'])->update([
            'status' => OrderStatus::Issued->value,
        ]);

        $issued = $readPort->listForMasterFunnel($flow['masterId'], 'completed');
        $this->assertSame(0, $issued['meta']['total']);
        $this->assertSame([], $issued['items']);
        $this->assertSame(0, $readPort->countsForMaster($flow['masterId'])->completed);
        $this->assertNull($readPort->findCardById($flow['taskId']));
    }
}
