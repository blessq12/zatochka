<?php

namespace App\Filament\Order\Resources\OrderResource\Pages;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\OrderResource\Actions\OrderMutationActions;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    public function defaultInfolist(Schema $schema): Schema
    {
        return parent::defaultInfolist($schema->columns(1));
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();
        $record->loadMissing('client');

        $status = OrderStatus::tryFrom((string) $record->status);
        $statusLabel = $status?->label() ?? ((string) $record->status ?: '—');
        $statusColor = $status?->color() ?? 'gray';

        $meta = array_filter([
            OrderServiceType::tryLabel($record->service_type),
            OrderBillingType::tryLabel($record->billing_type),
            $record->urgency === OrderUrgency::Urgent->value ? OrderUrgency::Urgent->label() : null,
            $this->clientSubheading($record),
        ], static fn(?string $part): bool => filled($part));

        $badge = Blade::render(
            '<x-filament::badge :color="$color" size="lg">{{ $label }}</x-filament::badge>',
            [
                'color' => $statusColor,
                'label' => $statusLabel,
            ],
        );

        $metaHtml = $meta === []
            ? ''
            : '<span class="text-sm text-gray-500 dark:text-gray-400">' . e(implode(' · ', $meta)) . '</span>';

        return new HtmlString(
            '<div class="flex flex-wrap items-center gap-x-3 gap-y-1">' . $badge . '</div>'
        );
    }

    private function clientSubheading(OrderModel $record): string
    {
        $client = $record->client;

        if ($client === null) {
            return 'Клиент #' . $record->client_id;
        }

        $name = filled($client->name) ? (string) $client->name : 'Без имени';
        $phone = filled($client->phone) ? (string) $client->phone : null;

        return $phone === null ? $name : $name . ' · ' . $phone;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Действия')
                ->icon(Heroicon::OutlinedBolt)
                ->columnSpanFull()
                ->schema([
                    Text::make('Нет активных действий')
                        ->color('gray')
                        ->visible(fn (): bool => ! $this->hasVisibleRailActions()),
                    SchemaActions::make(fn (): array => $this->configureRailActions([
                        ...OrderMutationActions::orderLifecycle(),
                        ...OrderMutationActions::equipment(),
                        ...OrderMutationActions::pricing(),
                        ...OrderMutationActions::inventory(),
                    ]))->visible(fn (): bool => $this->hasVisibleRailActions()),
                ]),
            EmbeddedSchema::make('infolist'),
            $this->getRelationManagersContentComponent(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('К списку')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(OrderResource::getUrl('index')),
        ];
    }

    private function hasVisibleRailActions(): bool
    {
        foreach (
            [
                ...OrderMutationActions::orderLifecycle(),
                ...OrderMutationActions::equipment(),
                ...OrderMutationActions::pricing(),
                ...OrderMutationActions::inventory(),
            ] as $action
        ) {
            $action = $action->record($this->getRecord());

            if ($action->isVisible()) {
                return true;
            }
        }

        return false;
    }

    /** @param  list<Action>  $actions
     * @return list<Action>
     */
    private function configureRailActions(array $actions): array
    {
        $refresh = function (): void {
            $this->getRecord()->refresh()->load([
                'client',
                'items.equipment.components',
                'items.reception',
                'warrantySourceOrder',
            ]);
        };

        return array_map(
            function (Action $action) use ($refresh): Action {
                return $action
                    ->record($this->getRecord())
                    ->after($refresh)
                    ->button();
            },
            $actions,
        );
    }
}
