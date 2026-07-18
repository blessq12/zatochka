<?php

namespace App\Filament\SiteContent\Pages;

use App\Application\SiteContent\Command\ReplaceServicePriceListCommand;
use App\Application\SiteContent\Command\ReplaceServicePriceListHandler;
use App\Domain\SiteContent\VO\PriceBlockType;
use App\Domain\SiteContent\VO\PricePrefix;
use App\Infrastructure\SiteContent\Model\PriceBlockModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class ManageServicePriceList extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Прайс услуг';

    protected static ?string $title = 'Прайс услуг';

    protected static ?string $slug = 'service-price-list';

    protected static ?int $navigationSort = 50;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $blocks = PriceBlockModel::query()
            ->with('items')
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (PriceBlockModel $block): array => [
                'id' => (int) $block->id,
                'type' => (string) $block->type,
                'title' => (string) $block->title,
                'items' => $block->items->map(static fn ($item): array => [
                    'id' => (int) $item->id,
                    'name' => (string) $item->name,
                    'price' => (string) $item->price,
                    'prefix' => $item->prefix,
                    'description' => $item->description,
                ])->values()->all(),
            ])
            ->all();

        $this->form->fill(['blocks' => $blocks]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('blocks')
                    ->label('Блоки прайса')
                    ->schema([
                        TextInput::make('id')->hidden(),
                        Select::make('type')
                            ->label('Тип')
                            ->options(PriceBlockType::options())
                            ->required(),
                        TextInput::make('title')->label('Заголовок')->required(),
                        Repeater::make('items')
                            ->label('Позиции')
                            ->schema([
                                TextInput::make('id')->hidden(),
                                TextInput::make('name')->label('Название')->required(),
                                TextInput::make('price')->label('Цена')->required(),
                                Select::make('prefix')
                                    ->label('Префикс')
                                    ->options(PricePrefix::options())
                                    ->nullable(),
                                Textarea::make('description')->label('Описание')->rows(2)->nullable(),
                            ])
                            ->collapsible()
                            ->default([]),
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
            app(ReplaceServicePriceListHandler::class)->handle(new ReplaceServicePriceListCommand(
                array_values((array) ($state['blocks'] ?? [])),
            ));
            Notification::make()->success()->title('Прайс сохранён')->send();
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
