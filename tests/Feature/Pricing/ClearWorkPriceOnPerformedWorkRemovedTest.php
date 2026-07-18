<?php

namespace Tests\Feature\Pricing;

use App\Application\Pricing\Command\SetWorkPriceCommand;
use App\Application\Pricing\Command\SetWorkPriceHandler;
use App\Application\Workshop\Command\SyncOrderPerformedWorkItem;
use App\Application\Workshop\Command\SyncOrderPerformedWorksCommand;
use App\Application\Workshop\Command\SyncOrderPerformedWorksHandler;
use App\Infrastructure\Pricing\Model\WorkPriceModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class ClearWorkPriceOnPerformedWorkRemovedTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_removing_performed_work_via_sync_clears_its_work_price(): void
    {
        $master = $this->createMaster('clear-price@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);
        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $workId = (int) PerformedWorkModel::query()
            ->where('production_task_id', $flow['taskId'])
            ->value('id');

        app(SetWorkPriceHandler::class)->handle(new SetWorkPriceCommand(
            $workId,
            '250.00',
        ));

        $this->assertDatabaseHas('work_prices', [
            'performed_work_id' => $workId,
        ]);

        app(SyncOrderPerformedWorksHandler::class)->handle(new SyncOrderPerformedWorksCommand(
            $flow['orderId'],
            [],
        ));

        $this->assertDatabaseMissing('performed_works', ['id' => $workId]);
        $this->assertSame(0, WorkPriceModel::query()->where('performed_work_id', $workId)->count());
    }
}
