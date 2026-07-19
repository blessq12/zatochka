<?php

namespace App\Filament\Documents\Pages;

use App\Filament\Documents\Clusters\DocumentsCluster;
use App\Filament\Documents\Tables\LegalDocumentsTable;
use App\Filament\Documents\Tables\PrintDocumentsTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

final class ManageDocuments extends Page
{
    protected static ?string $cluster = DocumentsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Документы';

    protected static ?string $title = 'Документы';

    protected static ?string $slug = 'manage';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('documents')
                ->persistTabInQueryString('tab')
                ->contained(false)
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Юридические документы')
                        ->icon(Heroicon::OutlinedScale)
                        ->schema([
                            Livewire::make(LegalDocumentsTable::class),
                        ]),
                    Tab::make('Печатные документы')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->schema([
                            Livewire::make(PrintDocumentsTable::class),
                        ]),
                ]),
        ]);
    }
}
