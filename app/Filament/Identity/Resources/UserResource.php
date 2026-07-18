<?php

namespace App\Filament\Identity\Resources;

use App\Application\Identity\Command\ChangeStaffPasswordCommand;
use App\Application\Identity\Command\ChangeStaffPasswordHandler;
use App\Filament\Identity\Resources\UserResource\Pages\CreateUser;
use App\Filament\Identity\Resources\UserResource\Pages\EditUser;
use App\Filament\Identity\Resources\UserResource\Pages\ListUsers;
use App\Filament\Support\CatalogResource;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends CatalogResource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Сотрудники';

    protected static ?string $navigationLabel = 'Сотрудники';

    protected static ?string $modelLabel = 'Сотрудник';

    protected static ?string $pluralModelLabel = 'Сотрудники';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 10;

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Имя')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Эл. почта')
                ->email()
                ->required()
                ->maxLength(255),
            Select::make('role')
                ->label('Роль')
                ->options([
                    UserRole::Manager->value => 'Менеджер',
                    UserRole::Master->value => 'Мастер',
                ])
                ->required(),
            TextInput::make('password')
                ->label('Пароль')
                ->password()
                ->revealable()
                ->required(fn(string $operation): bool => $operation === 'create')
                ->dehydrated(fn(?string $state): bool => filled($state))
                ->minLength(8)
                ->helperText(fn(string $operation): ?string => $operation === 'edit'
                    ? 'Оставьте пустым, если не нужно менять пароль'
                    : null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Эл. почта')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->formatStateUsing(fn(UserRole|string $state): string => match ($state instanceof UserRole ? $state : UserRole::from((string) $state)) {
                        UserRole::Manager => 'Менеджер',
                        UserRole::Master => 'Мастер',
                        UserRole::Client => 'Клиент',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать'),
                Action::make('changePassword')
                    ->label('Сменить пароль')
                    ->icon(Heroicon::OutlinedKey)
                    ->form([
                        TextInput::make('password')
                            ->label('Новый пароль')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8),
                    ])
                    ->action(function (User $record, array $data): void {
                        try {
                            app(ChangeStaffPasswordHandler::class)->handle(new ChangeStaffPasswordCommand(
                                (int) $record->id,
                                (string) $data['password'],
                            ));
                            Notification::make()->title('Пароль обновлён')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                DeleteAction::make()->label('Удалить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
