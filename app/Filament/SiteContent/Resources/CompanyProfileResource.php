<?php

namespace App\Filament\SiteContent\Resources;

use App\Filament\SiteContent\Resources\CompanyProfileResource\Pages\EditCompanyProfile;
use App\Filament\SiteContent\Resources\CompanyProfileResource\Pages\ListCompanyProfiles;
use App\Filament\Support\DomainResource;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CompanyProfileResource extends DomainResource
{
    protected static ?string $model = CompanyProfileModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Компания';

    protected static ?string $modelLabel = 'Компания';

    protected static ?string $pluralModelLabel = 'Компания';

    protected static ?int $navigationSort = 10;

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('owner_name')
                ->label('Владелец')
                ->required()
                ->maxLength(255),
            TextInput::make('inn')
                ->label('ИНН')
                ->required()
                ->maxLength(32),
            TextInput::make('ogrn')
                ->label('ОГРН')
                ->required()
                ->maxLength(32),
            Textarea::make('legal_address')
                ->label('Юридический адрес')
                ->required()
                ->rows(2),
            Textarea::make('actual_address')
                ->label('Фактический адрес')
                ->required()
                ->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner_name')->label('Владелец'),
                TextColumn::make('inn')->label('ИНН'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyProfiles::route('/'),
            'edit' => EditCompanyProfile::route('/{record}/edit'),
        ];
    }
}
