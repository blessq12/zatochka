<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalDocumentResource\Pages;
use App\Models\LegalDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Юр. документы';

    protected static ?string $modelLabel = 'Юридический документ';

    protected static ?string $pluralModelLabel = 'Юридические документы';

    protected static ?string $navigationGroup = 'Организация';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Документ')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Компания')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Тип документа')
                            ->options(LegalDocument::getAvailableTypes())
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->maxLength(255)
                            ->placeholder('Опционально'),
                        Forms\Components\TextInput::make('version')
                            ->label('Версия')
                            ->maxLength(255)
                            ->placeholder('Напр. 1.0'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Текст документа')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Компания')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LegalDocument::getAvailableTypes()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(40)
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Содержание')
                    ->limit(50)
                    ->formatStateUsing(fn (?string $state): string => $state ? strip_tags($state) : '')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('version')
                    ->label('Версия')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Компания')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип документа')
                    ->options(LegalDocument::getAvailableTypes()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalDocuments::route('/'),
            'create' => Pages\CreateLegalDocument::route('/create'),
            'edit' => Pages\EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
