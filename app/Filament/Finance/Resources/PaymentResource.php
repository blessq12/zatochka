<?php

namespace App\Filament\Finance\Resources;

use App\Application\Finance\Command\AcceptPaymentCommand;
use App\Application\Finance\Command\AcceptPaymentHandler;
use App\Application\Finance\Command\CreateRefundCommand;
use App\Application\Finance\Command\CreateRefundHandler;
use App\Filament\Finance\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Finance\Resources\PaymentResource\Pages\ViewPayment;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Finance\Model\PaymentModel;
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

class PaymentResource extends DomainResource
{
    protected static ?string $model = PaymentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Платежи';

    protected static ?string $modelLabel = 'Платёж';

    protected static ?string $pluralModelLabel = 'Платежи';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 10;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('order_id'),
            TextEntry::make('amount'),
            TextEntry::make('currency'),
            TextEntry::make('method'),
            TextEntry::make('accepted_at')->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('order_id')->sortable(),
                TextColumn::make('amount'),
                TextColumn::make('currency'),
                TextColumn::make('method')->searchable(),
                TextColumn::make('accepted_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('refund')
                    ->label('Refund')
                    ->color('danger')
                    ->form([
                        TextInput::make('amount')->required(),
                        TextInput::make('currency')->default('RUB')->required(),
                        TextInput::make('reason'),
                    ])
                    ->action(function (PaymentModel $record, array $data): void {
                        try {
                            $refundId = app(SequentialEntityIdGenerator::class)->next('refund')->value;
                            app(CreateRefundHandler::class)->handle(new CreateRefundCommand(
                                (int) $record->id,
                                $refundId,
                                (string) $data['amount'],
                                $data['currency'] ?? 'RUB',
                                $data['reason'] ?? null,
                            ));
                            Notification::make()->title('Refund created')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getHeaderActions(): array
    {
        return [
            Action::make('acceptPayment')
                ->label('Accept payment')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    TextInput::make('orderId')->required()->label('ID заказа')->length(32),
                    TextInput::make('amount')->required(),
                    Select::make('method')->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'transfer' => 'Transfer',
                    ])->required(),
                    TextInput::make('currency')->default('RUB')->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $paymentId = app(SequentialEntityIdGenerator::class)->next('payment')->value;
                        app(AcceptPaymentHandler::class)->handle(new AcceptPaymentCommand(
                            $paymentId,
                            (string) $data['orderId'],
                            (string) $data['amount'],
                            $data['method'],
                            $data['currency'] ?? 'RUB',
                        ));
                        Notification::make()->title('Payment accepted')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }
}
