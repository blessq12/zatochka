<?php

namespace App\Filament\SiteContent\Pages;

use App\Application\SiteContent\Command\ReplaceFaqCatalogCommand;
use App\Application\SiteContent\Command\ReplaceFaqCatalogHandler;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
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
final class ManageFaqCatalog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $title = 'Частые вопросы';

    protected static ?string $slug = 'faq';

    protected static ?int $navigationSort = 60;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $items = FaqItemModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (FaqItemModel $item): array => [
                'id' => (int) $item->id,
                'question' => (string) $item->question,
                'answer_lines' => implode("\n", array_values((array) $item->answer_lines)),
            ])
            ->all();

        $this->form->fill(['items' => $items]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('items')
                    ->label('Вопросы')
                    ->schema([
                        TextInput::make('id')->hidden(),
                        TextInput::make('question')->label('Вопрос')->required(),
                        Textarea::make('answer_lines')
                            ->label('Ответ')
                            ->helperText('Каждая строка — отдельный абзац')
                            ->required()
                            ->rows(4),
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
            $items = [];

            foreach (array_values((array) ($state['items'] ?? [])) as $item) {
                $items[] = [
                    'id' => $item['id'] ?? null,
                    'question' => (string) ($item['question'] ?? ''),
                    'answer_lines' => (string) ($item['answer_lines'] ?? ''),
                ];
            }

            app(ReplaceFaqCatalogHandler::class)->handle(new ReplaceFaqCatalogCommand($items));
            Notification::make()->success()->title('FAQ сохранён')->send();
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
