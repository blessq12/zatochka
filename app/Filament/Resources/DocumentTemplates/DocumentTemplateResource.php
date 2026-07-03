<?php

namespace App\Filament\Resources\DocumentTemplates;

use App\Filament\Clusters\OrderFulfillmentCluster;
use App\Filament\Resources\DocumentTemplates\Pages\EditDocumentTemplate;
use App\Filament\Resources\DocumentTemplates\Pages\ListDocumentTemplates;
use App\Filament\Resources\DocumentTemplates\Schemas\DocumentTemplateForm;
use App\Filament\Resources\DocumentTemplates\Tables\DocumentTemplatesTable;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\DocumentTemplateModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentTemplateResource extends Resource
{
    protected static ?string $cluster = OrderFulfillmentCluster::class;

    protected static ?string $model = DocumentTemplateModel::class;

    protected static ?string $navigationLabel = 'Документы';

    protected static ?string $slug = 'document-templates';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'шаблон документа';

    protected static ?string $pluralModelLabel = 'Документы';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return DocumentTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentTemplates::route('/'),
            'edit' => EditDocumentTemplate::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('updatedBy');
    }
}
