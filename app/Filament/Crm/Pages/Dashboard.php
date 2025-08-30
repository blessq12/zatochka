<?php

namespace App\Filament\Crm\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';


    public function getTitle(): string
    {
        return 'Панель управления';
    }

    public function getHeading(): string
    {
        return 'Добро пожаловать в панель менеджера';
    }
}
