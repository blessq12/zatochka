<?php

namespace App\Filament\Resources\DocumentTemplates\Tables;

use App\Domain\OrderFulfillment\Enum\DocumentType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Документ')
                    ->formatStateUsing(fn (string $state): string => DocumentType::from($state)->label()),
                TextColumn::make('updated_at')
                    ->label('Изменён')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
                TextColumn::make('updatedBy.name')
                    ->label('Кто изменил')
                    ->formatStateUsing(fn ($state, $record): string => $record->updatedBy
                        ? trim($record->updatedBy->name.' '.$record->updatedBy->surname)
                        : '—'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
