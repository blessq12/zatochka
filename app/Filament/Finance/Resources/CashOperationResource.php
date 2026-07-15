<?php

namespace App\Filament\Finance\Resources;

use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Filament\Finance\Resources\CashOperationResource\Pages\ListCashOperations;
use App\Filament\Finance\Resources\CashOperationResource\Pages\ViewCashOperation;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CashOperationResource extends DomainResource
{
    protected static ?string $model = CashOperationModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Касса';

    protected static ?string $modelLabel = 'Кассовая операция';

    protected static ?string $pluralModelLabel = 'Касса';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 20;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('type')->badge(),
            TextEntry::make('amount'),
            TextEntry::make('currency'),
            TextEntry::make('comment')->placeholder('-'),
            TextEntry::make('registered_at')->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('type')->searchable()->badge(),
                TextColumn::make('amount'),
                TextColumn::make('currency'),
                TextColumn::make('registered_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getHeaderActions(): array
    {
        return [
            Action::make('registerCashOperation')
                ->label('Register cash operation')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    Select::make('type')->options([
                        'in' => 'In',
                        'out' => 'Out',
                    ])->required(),
                    TextInput::make('amount')->required(),
                    TextInput::make('currency')->default('RUB')->required(),
                    TextInput::make('comment'),
                ])
                ->action(function (array $data): void {
                    try {
                        $id = app(SequentialEntityIdGenerator::class)->next('cash_operation')->value;
                        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
                            $id,
                            $data['type'],
                            (string) $data['amount'],
                            $data['currency'] ?? 'RUB',
                            $data['comment'] ?? null,
                        ));
                        Notification::make()->title('Cash operation registered')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashOperations::route('/'),
            'view' => ViewCashOperation::route('/{record}'),
        ];
    }
}
