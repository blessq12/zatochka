<?php

namespace App\Application\Workshop\Presenter;

use App\Application\Workshop\DTO\ProductionTaskDTO;

interface ProductionTaskPresenter
{
    /** @return array<string, mixed> */
    public function present(ProductionTaskDTO $task): array;

    /**
     * @param list<ProductionTaskDTO> $tasks
     * @return list<array<string, mixed>>
     */
    public function presentCollection(array $tasks): array;
}
