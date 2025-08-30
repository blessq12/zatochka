<?php

namespace App\Filament\Workshop\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public function getTitle(): string
    {
        return 'Панель мастера';
    }

    public function getHeading(): string
    {
        return 'Добро пожаловать в панель мастера';
    }
}
