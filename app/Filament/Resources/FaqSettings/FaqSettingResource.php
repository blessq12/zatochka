<?php

namespace App\Filament\Resources\FaqSettings;

use App\Filament\Resources\FaqSettings\Pages\EditFaqSetting;
use App\Filament\SiteSettings\Schemas\FaqSettingForm;
use App\Filament\Support\AbstractSiteSettingResource;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FaqSettingResource extends AbstractSiteSettingResource
{
    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $slug = 'faq';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    public static function settingKey(): string
    {
        return 'faq';
    }

    public static function form(Schema $schema): Schema
    {
        return FaqSettingForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditFaqSetting::route('/'),
        ];
    }
}
