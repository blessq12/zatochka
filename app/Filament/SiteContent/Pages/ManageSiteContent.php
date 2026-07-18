<?php

namespace App\Filament\SiteContent\Pages;

use App\Application\SiteContent\Command\ReplaceFaqCatalogCommand;
use App\Application\SiteContent\Command\ReplaceFaqCatalogHandler;
use App\Application\SiteContent\Command\ReplaceWorkScheduleCommand;
use App\Application\SiteContent\Command\ReplaceWorkScheduleHandler;
use App\Application\SiteContent\Command\UpdateCompanyProfileCommand;
use App\Application\SiteContent\Command\UpdateCompanyProfileHandler;
use App\Application\SiteContent\Command\UpdateDeliveryInfoCommand;
use App\Application\SiteContent\Command\UpdateDeliveryInfoHandler;
use App\Application\SiteContent\Command\UpdateSiteContactsCommand;
use App\Application\SiteContent\Command\UpdateSiteContactsHandler;
use App\Filament\SiteContent\Clusters\SiteContentCluster;
use App\Filament\SiteContent\Support\SiteContentFormSchemas;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * @property-read Schema $form
 */
final class ManageSiteContent extends Page
{
    protected static ?string $cluster = SiteContentCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Контент сайта';

    protected static ?string $title = 'Контент сайта';

    protected static ?string $slug = 'content';

    protected static ?int $navigationSort = 10;

    protected Width|string|null $maxContentWidth = Width::Full;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadFormData());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('siteContent')
                    ->persistTabInQueryString('tab')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Компания')
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->schema(SiteContentFormSchemas::company()),
                        Tab::make('Контакты')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema(SiteContentFormSchemas::contacts()),
                        Tab::make('Доставка')
                            ->icon(Heroicon::OutlinedTruck)
                            ->schema(SiteContentFormSchemas::delivery()),
                        Tab::make('График')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->schema(SiteContentFormSchemas::schedule()),
                        Tab::make('FAQ')
                            ->icon(Heroicon::OutlinedQuestionMarkCircle)
                            ->schema(SiteContentFormSchemas::faq()),
                    ]),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();

            $this->saveCompany((array) ($state['company'] ?? []));
            $this->saveContacts((array) ($state['contacts'] ?? []));
            $this->saveDelivery((array) ($state['delivery'] ?? []));
            $this->saveSchedule((array) ($state['schedule'] ?? []));
            $this->saveFaq((array) ($state['faq'] ?? []));

            Notification::make()->success()->title('Контент сайта сохранён')->send();
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

    /** @return array<string, mixed> */
    private function loadFormData(): array
    {
        $company = CompanyProfileModel::query()->find(1);
        $contacts = SiteContactsModel::query()->find(1);
        $delivery = DeliveryInfoModel::query()->find(1);

        return [
            'company' => [
                'owner_name' => (string) ($company?->owner_name ?? ''),
                'inn' => (string) ($company?->inn ?? ''),
                'ogrn' => (string) ($company?->ogrn ?? ''),
                'legal_address' => (string) ($company?->legal_address ?? ''),
                'actual_address' => (string) ($company?->actual_address ?? ''),
            ],
            'contacts' => [
                'contact_person' => (string) ($contacts?->contact_person ?? ''),
                'phone' => (string) ($contacts?->phone ?? ''),
                'email' => (string) ($contacts?->email ?? ''),
                'address_main' => (string) ($contacts?->address_main ?? ''),
                'entrance_directions' => (string) ($contacts?->entrance_directions ?? ''),
                'social_links' => array_values((array) ($contacts?->social_links ?? [])),
            ],
            'delivery' => [
                'free_conditions' => array_values((array) ($delivery?->free_conditions ?? [])),
                'advantages' => array_values(array_map(
                    static fn (mixed $advantage): array => [
                        'title' => (string) (is_array($advantage) ? ($advantage['title'] ?? '') : ''),
                        'description' => (string) (is_array($advantage) ? ($advantage['description'] ?? '') : ''),
                    ],
                    (array) ($delivery?->advantages ?? []),
                )),
            ],
            'schedule' => [
                'days' => ScheduleDayModel::query()
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
                    ->all(),
            ],
            'faq' => [
                'items' => FaqItemModel::query()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(static fn (FaqItemModel $item): array => [
                        'id' => (int) $item->id,
                        'question' => (string) $item->question,
                        'answer_lines' => implode("\n", array_values((array) $item->answer_lines)),
                    ])
                    ->all(),
            ],
        ];
    }

    /** @param array<string, mixed> $company */
    private function saveCompany(array $company): void
    {
        app(UpdateCompanyProfileHandler::class)->handle(new UpdateCompanyProfileCommand(
            (string) ($company['owner_name'] ?? ''),
            (string) ($company['inn'] ?? ''),
            (string) ($company['ogrn'] ?? ''),
            (string) ($company['legal_address'] ?? ''),
            (string) ($company['actual_address'] ?? ''),
        ));
    }

    /** @param array<string, mixed> $contacts */
    private function saveContacts(array $contacts): void
    {
        app(UpdateSiteContactsHandler::class)->handle(new UpdateSiteContactsCommand(
            (string) ($contacts['contact_person'] ?? ''),
            (string) ($contacts['phone'] ?? ''),
            (string) ($contacts['email'] ?? ''),
            (string) ($contacts['address_main'] ?? ''),
            (string) ($contacts['entrance_directions'] ?? ''),
            array_values((array) ($contacts['social_links'] ?? [])),
        ));
    }

    /** @param array<string, mixed> $delivery */
    private function saveDelivery(array $delivery): void
    {
        $conditions = [];

        foreach ((array) ($delivery['free_conditions'] ?? []) as $row) {
            $conditions[] = is_array($row)
                ? (string) ($row['value'] ?? $row['condition'] ?? '')
                : (string) $row;
        }

        $advantages = [];

        foreach ((array) ($delivery['advantages'] ?? []) as $advantage) {
            if (! is_array($advantage)) {
                continue;
            }

            $advantages[] = [
                'title' => (string) ($advantage['title'] ?? ''),
                'description' => (string) ($advantage['description'] ?? ''),
            ];
        }

        app(UpdateDeliveryInfoHandler::class)->handle(new UpdateDeliveryInfoCommand(
            $conditions,
            $advantages,
        ));
    }

    /** @param array<string, mixed> $schedule */
    private function saveSchedule(array $schedule): void
    {
        app(ReplaceWorkScheduleHandler::class)->handle(new ReplaceWorkScheduleCommand(
            array_values((array) ($schedule['days'] ?? [])),
        ));
    }

    /** @param array<string, mixed> $faq */
    private function saveFaq(array $faq): void
    {
        $items = [];

        foreach (array_values((array) ($faq['items'] ?? [])) as $item) {
            $items[] = [
                'id' => $item['id'] ?? null,
                'question' => (string) ($item['question'] ?? ''),
                'answer_lines' => (string) ($item['answer_lines'] ?? ''),
            ];
        }

        app(ReplaceFaqCatalogHandler::class)->handle(new ReplaceFaqCatalogCommand($items));
    }
}
