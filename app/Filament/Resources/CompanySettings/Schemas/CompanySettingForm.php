<?php

namespace App\Filament\Resources\CompanySettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CompanySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('value_json')
                    ->label('JSON')
                    ->required()
                    ->rows(20)
                    ->columnSpanFull(),
            ]);
    }
}
