<?php

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Table::configureUsing(static function (Table $table): void {
            $table
                ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
                ->recordActionsAlignment('start')
                ->recordActionsColumnLabel('')
                ->modifyUngroupedRecordActionsUsing(static function (Action $action): void {
                    $action
                        ->iconButton()
                        ->tooltip(static fn (Action $action): string|Htmlable|null => $action->getLabel());
                });
        });
    }
}
