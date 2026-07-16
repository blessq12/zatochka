<?php

namespace Tests\Feature\Workshop;

use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\VO\ProductionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class SharpeningFlowSmokeTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_sharpening_create_assign_work_finish_to_works_completed(): void
    {
        $master = $this->createMaster('sharpening-master@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->startWork($flow['taskId']);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::InProgress);
        $this->assertTaskStatus($flow['taskId'], ProductionStatus::InWork);

        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $this->assertTaskStatus($flow['taskId'], ProductionStatus::Completed);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::WorksCompleted);
    }
}
