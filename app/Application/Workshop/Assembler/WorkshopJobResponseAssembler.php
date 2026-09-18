<?php

namespace App\Application\Workshop\Assembler;

use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Aggregate\WorkshopJob;

final readonly class WorkshopJobResponseAssembler
{
    public function assemble(WorkshopJob $job): WorkshopJobResponse
    {
        $items = [];
        foreach ($job->items() as $item) {
            $works = [];
            foreach ($item->works() as $work) {
                $works[] = [
                    'id' => $work->id(),
                    'title' => $work->title(),
                    'position' => $work->position(),
                    'equipment_module_id' => $work->equipmentModuleId(),
                ];
            }

            $items[] = [
                'id' => $item->id(),
                'order_item_id' => $item->orderItemId(),
                'completed_qty' => $item->completedQty(),
                'works' => $works,
            ];
        }

        return new WorkshopJobResponse(
            (int) $job->id(),
            $job->orderId(),
            $job->masterId(),
            $job->status()->value,
            $items,
        );
    }
}
