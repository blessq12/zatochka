<?php

namespace App\Filament\Delivery\Resources;

use App\Application\Delivery\Command\AssignCourierCommand;
use App\Application\Delivery\Command\AssignCourierHandler;
use App\Application\Delivery\Command\MarkEquipmentCollectedCommand;
use App\Application\Delivery\Command\MarkEquipmentCollectedHandler;
use App\Application\Delivery\Command\MarkOrderDeliveredCommand;
use App\Application\Delivery\Command\MarkOrderDeliveredHandler;
use App\Application\Delivery\Command\RequestDeliveryCommand;
use App\Application\Delivery\Command\RequestDeliveryHandler;
use App\Filament\Delivery\Resources\DeliveryRequestResource\Pages\ListDeliveryRequests;
use App\Filament\Delivery\Resources\DeliveryRequestResource\Pages\ViewDeliveryRequest;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Delivery\Model\DeliveryRequestModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DeliveryRequestResource extends DomainResource
{
    protected static ?string $model = DeliveryRequestModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Заказы';

    protected static ?string $navigationLabel = 'Доставка';

    protected static ?string $modelLabel = 'Заявка на доставку';

    protected static ?string $pluralModelLabel = 'Доставка';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 20;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('order_id'),
            TextEntry::make('status')->badge(),
            TextEntry::make('pickup')->badge(),
            TextEntry::make('city'),
            TextEntry::make('street'),
            TextEntry::make('building'),
            TextEntry::make('apartment')->placeholder('-'),
            TextEntry::make('courier_id')->placeholder('-'),
            TextEntry::make('comment')->placeholder('-'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('order_id')->sortable(),
                TextColumn::make('status')->searchable()->badge(),
                TextColumn::make('city'),
                TextColumn::make('courier_id'),
                IconColumn::make('pickup')->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('assignCourier')
                    ->label('Assign courier')
                    ->form([TextInput::make('courierId')->numeric()->required()->label('Courier ID')])
                    ->action(function (DeliveryRequestModel $record, array $data): void {
                        try {
                            app(AssignCourierHandler::class)->handle(new AssignCourierCommand(
                                (int) $record->id,
                                (int) $data['courierId'],
                            ));
                            Notification::make()->title('Courier assigned')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('markCollected')
                    ->label('Mark collected')
                    ->action(function (DeliveryRequestModel $record): void {
                        try {
                            app(MarkEquipmentCollectedHandler::class)->handle(
                                new MarkEquipmentCollectedCommand((int) $record->id),
                            );
                            Notification::make()->title('Equipment collected')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('markDelivered')
                    ->label('Mark delivered')
                    ->action(function (DeliveryRequestModel $record): void {
                        try {
                            app(MarkOrderDeliveredHandler::class)->handle(
                                new MarkOrderDeliveredCommand((int) $record->id),
                            );
                            Notification::make()->title('Order delivered')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getHeaderActions(): array
    {
        return [
            Action::make('requestDelivery')
                ->label('Request delivery')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    TextInput::make('orderId')->required()->label('ID заказа')->length(32),
                    TextInput::make('city')->required(),
                    TextInput::make('street')->required(),
                    TextInput::make('building')->required(),
                    TextInput::make('apartment'),
                    TextInput::make('comment'),
                    Checkbox::make('pickup')->label('Pickup'),
                ])
                ->action(function (array $data): void {
                    try {
                        $id = app(SequentialEntityIdGenerator::class)->next('delivery_request')->value;
                        app(RequestDeliveryHandler::class)->handle(new RequestDeliveryCommand(
                            $id,
                            (string) $data['orderId'],
                            $data['city'],
                            $data['street'],
                            $data['building'],
                            $data['apartment'] ?? null,
                            $data['comment'] ?? null,
                            (bool) ($data['pickup'] ?? false),
                        ));
                        Notification::make()->title('Delivery requested')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryRequests::route('/'),
            'view' => ViewDeliveryRequest::route('/{record}'),
        ];
    }
}
