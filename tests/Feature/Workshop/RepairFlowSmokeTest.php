<?php

namespace Tests\Feature\Workshop;

use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\VO\ProductionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class RepairFlowSmokeTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_repair_create_assign_component_work_finish_to_works_completed(): void
    {
        $master = $this->createMaster('repair-master@test.local');
        $flow = $this->createRepairOrderWithMaster($master);

        $this->startWork($flow['taskId']);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::InProgress);

        $this->addRepairWork($flow['taskId'], $flow['masterId'], $flow['componentId']);
        $this->finishTask($flow['taskId']);

        $this->assertTaskStatus($flow['taskId'], ProductionStatus::Completed);
        $this->assertOrderStatus($flow['orderId'], OrderStatus::WorksCompleted);
    }
}
