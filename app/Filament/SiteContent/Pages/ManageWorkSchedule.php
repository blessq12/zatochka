<?php

namespace App\Filament\SiteContent\Pages;

use App\Application\SiteContent\Command\ReplaceWorkScheduleCommand;
use App\Application\SiteContent\Command\ReplaceWorkScheduleHandler;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class ManageWorkSchedule extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'График работы';

    protected static ?string $title = 'График работы';

    protected static ?string $slug = 'work-schedule';

    protected static ?int $navigationSort = 40;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $days = ScheduleDayModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (ScheduleDayModel $day): array => [
                'id' => (int) $day->id,
                'name' => (string) $day->name,
                'is_day_off' => (bool) $day->is_day_off,
                'day_off_text' => $day->day_off_text,
                'workshop' => $day->workshop,
                'delivery' => $day->delivery,
            ])
            ->all();

        $this->form->fill(['days' => $days]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('days')
                    ->label('Дни')
                    ->schema([
                        TextInput::make('id')->hidden(),
                        TextInput::make('name')->label('Название')->required(),
                        Toggle::make('is_day_off')->label('Выходной')->live(),
                        TextInput::make('day_off_text')
                            ->label('Текст выходного')
                            ->visible(fn (Get $get): bool => (bool) $get('is_day_off')),
                        TextInput::make('workshop')
                            ->label('Мастерская')
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_day_off')),
                        TextInput::make('delivery')
                            ->label('Доставка')
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_day_off')),
                    ])
                    ->collapsible()
                    ->default([]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();
            app(ReplaceWorkScheduleHandler::class)->handle(new ReplaceWorkScheduleCommand(
                array_values((array) ($state['days'] ?? [])),
            ));
            Notification::make()->success()->title('График сохранён')->send();
            $this->mount();
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        } catch (Throwable $e) {
            Notification::make()->danger()->title('Не удалось сохранить')->send();
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Сохранить')
                        ->submit('save'),
                ]),
            ]);
    }
}
