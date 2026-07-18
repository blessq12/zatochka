<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Pages;

use App\Filament\Feedback\Resources\ReviewResource;
use App\Filament\Feedback\Resources\ReviewResource\Actions\ReviewMutationActions;
use App\Filament\Feedback\Resources\ReviewResource\Support\ReviewPresentation;
use App\Infrastructure\Feedback\Model\ReviewModel;
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

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var ReviewModel $record */
        $record = $this->getRecord();

        return 'Отзыв · '.ReviewPresentation::orderNumberLabel($record);
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var ReviewModel $record */
        $record = $this->getRecord();

        $badge = Blade::render(
            '<x-filament::badge :color="$color">{{ $label }}</x-filament::badge>',
            [
                'color' => ReviewPresentation::statusColor($record->status),
                'label' => ReviewPresentation::statusLabel($record->status),
            ],
        );

        return new HtmlString(
            '<div class="flex flex-wrap items-center gap-x-3 gap-y-1">'.$badge.'</div>'
        );
    }

    public function defaultInfolist(Schema $schema): Schema
    {
        return parent::defaultInfolist($schema->columns(1));
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
                    SchemaActions::make(fn (): array => $this->configureRailActions(
                        ReviewMutationActions::all(),
                    ))->visible(fn (): bool => $this->hasVisibleRailActions()),
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
                ->url(ReviewResource::getUrl('index')),
        ];
    }

    private function hasVisibleRailActions(): bool
    {
        foreach (ReviewMutationActions::all() as $action) {
            $action = $action->record($this->getRecord());

            if ($action->isVisible()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<Action>  $actions
     * @return list<Action>
     */
    private function configureRailActions(array $actions): array
    {
        $refresh = function (): void {
            $this->getRecord()->refresh()->load(['client', 'order.items.equipment.components']);
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
