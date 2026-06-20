<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SiteSettingForm
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
