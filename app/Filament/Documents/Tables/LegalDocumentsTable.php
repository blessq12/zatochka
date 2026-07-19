<?php

namespace App\Filament\Documents\Tables;

use App\Application\Documents\Command\UpdateLegalDocumentCommand;
use App\Application\Documents\Command\UpdateLegalDocumentHandler;
use App\Domain\Documents\VO\DocumentType;
use App\Infrastructure\Documents\Model\LegalDocumentModel;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LegalDocumentsTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = '';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(fn (): Builder => LegalDocumentModel::query()->orderBy('type'))
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(
                        fn (?string $state): string => DocumentType::tryFrom((string) $state)?->label() ?? (string) $state
                    ),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Нет документов')
            ->emptyStateDescription('Юридические тексты ещё не загружены.')
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Редактировать')
                    ->modalHeading('Редактирование юридического документа')
                    ->modalSubmitActionLabel('Сохранить')
                    ->modalCancelActionLabel('Отмена')
                    ->successNotificationTitle('Сохранено')
                    ->modalWidth('5xl')
                    ->form([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('body_html')
                            ->label('Текст')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->using(function (Model $record, array $data): Model {
                        $type = DocumentType::from((string) $record->getKey());

                        app(UpdateLegalDocumentHandler::class)->handle(new UpdateLegalDocumentCommand(
                            type: $type,
                            title: (string) ($data['title'] ?? ''),
                            bodyHtml: (string) ($data['body_html'] ?? ''),
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
