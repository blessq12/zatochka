<?php

namespace App\Filament\Resources\Manager;

use App\Domain\Company\Enum\UserRole;
use App\Filament\Resources\Manager\UserResource\Pages;
use App\Filament\Resources\Manager\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Компания';
    protected static ?string $pluralLabel = 'Пользователи';
    protected static ?string $modelLabel = 'Пользователь';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя пользователя')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\CheckboxList::make('role')
                            ->label('Роли')
                            ->options(UserRole::getOptions())
                            ->default([UserRole::MANAGER->value])
                            ->columns(2)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Безопасность')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8),
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Роли')
                    ->badge()
                    ->color(function ($state): string {
                        $roles = is_array($state) ? $state : [$state];
                        if (in_array(UserRole::MANAGER->value, $roles) && in_array(UserRole::MASTER->value, $roles)) {
                            return 'info';
                        }
                        return in_array(UserRole::MANAGER->value, $roles) ? 'success' : 'warning';
                    })
                    ->formatStateUsing(function ($state): string {
                        $roles = is_array($state) ? $state : [$state];
                        return collect($roles)->map(fn($role) => UserRole::tryFrom($role)?->getLabel() ?? $role)->join(', ');
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email подтвержден')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все пользователи')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email')
                    ->placeholder('Все пользователи')
                    ->trueLabel('Подтвержденные')
                    ->falseLabel('Неподтвержденные'),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options(UserRole::getOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereJsonContains('role', $data['value']);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('is_deleted', false);
    }
}
