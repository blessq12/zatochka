<?php

namespace App\Filament\Resources\SiteContent\Pages;

use App\Filament\Resources\SiteContent\SiteContentResource;
use App\Filament\Support\SiteContentFormData;
use App\Infrastructure\Company\Persistence\Eloquent\SiteContentModel;
use Filament\Actions\Action;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class ManageSiteContent extends Page
{
    use CanUseDatabaseTransactions;
    use HasUnsavedDataChangesAlert;

    /** @var list<string> */
    private const SETTING_KEYS = [
        'contacts',
        'schedule',
        'company',
        'delivery_info',
        'faq',
    ];

    protected static string $resource = SiteContentResource::class;

    protected static ?string $title = 'Контент';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->fillForm();
    }

    public function hydrate(): void
    {
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canAccess(), 403);
    }

    protected function fillForm(): void
    {
        $settings = SiteContentModel::query()
            ->whereIn('key', self::SETTING_KEYS)
            ->get()
            ->mapWithKeys(fn (SiteContentModel $model): array => [
                $model->key => $model->value ?? [],
            ])
            ->all();

        $this->form->fill(SiteContentFormData::allToForm($settings));
    }

    public function save(): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            foreach (SiteContentFormData::allFromForm($data) as $key => $value) {
                SiteContentModel::query()
                    ->where('key', $key)
                    ->update(['value' => $value]);
            }
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();
        $this->rememberData();

        Notification::make()
            ->success()
            ->title('Контент сохранён')
            ->send();
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? 'Контент';
    }

    /**
     * @return array<NavigationItem>
     */
    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->inlineLabel($this->hasInlineLabels())
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return static::getResource()::form($schema);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): Component
    {
        return Actions::make($this->getFormActions())
            ->alignment($this->getFormActionsAlignment())
            ->fullWidth($this->hasFullWidthFormActions())
            ->sticky($this->areFormActionsSticky())
            ->key('form-actions');
    }

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
