<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ContextServiceProvider extends ServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    abstract protected function bindings(): array;

    public function register(): void
    {
        foreach ($this->bindings() as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
