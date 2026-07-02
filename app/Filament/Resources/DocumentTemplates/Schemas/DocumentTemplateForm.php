<?php

namespace App\Filament\Resources\DocumentTemplates\Schemas;

use App\Application\OrderFulfillment\Support\DocumentTemplateVariableCatalog;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class DocumentTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        $variablesHelp = collect(DocumentTemplateVariableCatalog::all())
            ->map(fn (array $item): string => '{{'.$item['key'].'}} — '.$item['label'])
            ->implode("\n");

        $loopsHelp = collect(DocumentTemplateVariableCatalog::loops())
            ->map(function (array $loop): string {
                $fields = implode(', ', array_map(
                    static fn (string $field): string => '{{'.$field.'}}',
                    $loop['fields'],
                ));

                return '{{#each '.$loop['key'].'}} ... '.$fields.' ... {{/each}} — '.$loop['label'];
            })
            ->implode("\n");

        return $schema
            ->columns(1)
            ->components([
                Section::make('Предпросмотр')
                    ->schema([
                        Select::make('preview_order_id')
                            ->label('Заказ для предпросмотра')
                            ->options(fn (): array => OrderModel::query()
                                ->orderByDesc('id')
                                ->limit(50)
                                ->pluck('order_number', 'id')
                                ->all())
                            ->searchable()
                            ->dehydrated(false),
                    ]),
                Section::make('Шаблон')
                    ->schema([
                        Textarea::make('body')
                            ->label('HTML-шаблон')
                            ->required()
                            ->rows(28)
                            ->helperText('Используйте переменные из секции ниже: {{order.number}}, {{client.name}} и т.д.')
                            ->extraAttributes(['style' => 'font-family: monospace; font-size: 12px;'])
                            ->columnSpanFull(),
                    ]),
                Section::make('Переменные')
                    ->collapsed()
                    ->description(new HtmlString(nl2br(e($variablesHelp."\n\nЦиклы:\n".$loopsHelp))))
                    ->schema([]),
            ]);
    }
}
