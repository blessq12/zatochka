<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Support\AbstractSiteSettingResource;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

abstract class EditSiteSettingRecord extends EditRecord
{
    abstract public static function settingKey(): string;

    abstract protected function valueToFormData(array $value): array;

    /** @return array<string, mixed> */
    abstract protected function formDataToValue(array $data): array;

    public function mount(int | string $record = 0): void
    {
        if ($record === 0) {
            /** @var AbstractSiteSettingResource $resource */
            $resource = static::getResource();
            $record = $resource::resolveSettingRecord()->getKey();
        }

        parent::mount($record);
    }

    public function getBreadcrumb(): string
    {
        return '';
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

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        if (isset($parameters['record'])) {
            return static::getResource()::canEdit($parameters['record']);
        }

        return static::getResource()::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return static::getResource()::getNavigationLabel();
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return static::getResource()::getNavigationIcon();
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->valueToFormData($this->getRecord()->value ?? []);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'value' => $this->formDataToValue($data),
        ]);

        return $record;
    }
}
