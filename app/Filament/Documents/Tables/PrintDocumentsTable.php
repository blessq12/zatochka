<?php

namespace App\Filament\Documents\Tables;

use App\Application\Documents\Command\UpdateDocumentTemplateCommand;
use App\Application\Documents\Command\UpdateDocumentTemplateHandler;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Infrastructure\Documents\Model\DocumentTemplateModel;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class PrintDocumentsTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = '';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(fn (): Builder => DocumentTemplateModel::query()->orderBy('id'))
            ->columns([
                TextColumn::make('kind')
                    ->label('Тип')
                    ->formatStateUsing(
                        fn (?string $state): string => PdfTemplateKind::tryFrom((string) $state)?->label() ?? (string) $state
                    ),
                TextColumn::make('name')
                    ->label('Название')
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Нет шаблонов')
            ->emptyStateDescription('Печатные шаблоны ещё не загружены.')
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Редактировать')
                    ->modalHeading('Редактирование печатного шаблона')
                    ->modalSubmitActionLabel('Сохранить')
                    ->modalCancelActionLabel('Отмена')
                    ->successNotificationTitle('Сохранено')
                    ->modalWidth('5xl')
                    ->form([
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                        Textarea::make('body_html')
                            ->label('Разметка шаблона')
                            ->required()
                            ->rows(20)
                            ->columnSpanFull(),
                    ])
                    ->using(function (Model $record, array $data): Model {
                        app(UpdateDocumentTemplateHandler::class)->handle(new UpdateDocumentTemplateCommand(
                            templateId: (string) $record->getKey(),
                            name: (string) ($data['name'] ?? ''),
                            bodyHtml: (string) ($data['body_html'] ?? ''),
                            isActive: (bool) ($data['is_active'] ?? false),
                        ));

                        return $record->refresh();
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return '';
    }
}
