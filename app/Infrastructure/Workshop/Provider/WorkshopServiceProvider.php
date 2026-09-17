<?php

namespace App\Infrastructure\Workshop\Provider;

use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Infrastructure\Workshop\Repository\EloquentWorkshopJobRepository;
use App\Providers\ContextServiceProvider;

final class WorkshopServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            WorkshopJobRepository::class => EloquentWorkshopJobRepository::class,
        ];
    }
}
