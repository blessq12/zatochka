<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MapPicker extends Field
{
    protected string $view = 'filament.forms.components.map-picker';
    
    protected ?string $latitudeField = null;
    protected ?string $longitudeField = null;

    public static function make(string $name = 'location'): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function latitude(string $field): static
    {
        $this->latitudeField = $field;
        return $this;
    }

    public function longitude(string $field): static
    {
        $this->longitudeField = $field;
        return $this;
    }

    public function getLatitudeField(): ?string
    {
        return $this->latitudeField;
    }

    public function getLongitudeField(): ?string
    {
        return $this->longitudeField;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}