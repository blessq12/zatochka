<?php

namespace App\Filament\Company\Schemas;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CompanySettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Настройки компании')
                    ->tabs([
                        Tab::make('Контакты')
                            ->schema([
                                Group::make(ContactsSettingForm::components())
                                    ->statePath('contacts')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('График работы')
                            ->schema([
                                Group::make(ScheduleSettingForm::components())
                                    ->statePath('schedule')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Компания')
                            ->schema([
                                Group::make(CompanyInfoSettingForm::components())
                                    ->statePath('company')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
