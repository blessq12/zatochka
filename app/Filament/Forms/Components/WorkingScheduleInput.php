<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class WorkingScheduleInput extends Field
{
    protected string $view = 'filament.forms.components.working-schedule-input';

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function getState(): array
    {
        $state = parent::getState();

        if (is_string($state)) {
            $decoded = json_decode($state, true);

            return is_array($decoded) ? $decoded : $this->getDefaultSchedule();
        }

        if (is_array($state)) {
            return $state;
        }

        return $this->getDefaultSchedule();
    }

    public function getDefaultSchedule(): array
    {
        return [
            'monday' => [
                'is_working' => true,
                'start' => '09:00',
                'end' => '18:00',
                'note' => 'Рабочий день',
            ],
            'tuesday' => [
                'is_working' => true,
                'start' => '09:00',
                'end' => '18:00',
                'note' => 'Рабочий день',
            ],
            'wednesday' => [
                'is_working' => true,
                'start' => '09:00',
                'end' => '18:00',
                'note' => 'Рабочий день',
            ],
            'thursday' => [
                'is_working' => true,
                'start' => '09:00',
                'end' => '18:00',
                'note' => 'Рабочий день',
            ],
            'friday' => [
                'is_working' => true,
                'start' => '09:00',
                'end' => '18:00',
                'note' => 'Рабочий день',
            ],
            'saturday' => [
                'is_working' => false,
                'start' => null,
                'end' => null,
                'note' => 'Выходной',
            ],
            'sunday' => [
                'is_working' => false,
                'start' => null,
                'end' => null,
                'note' => 'Выходной',
            ],
        ];
    }

    public function getDaysLabels(): array
    {
        return [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье',
        ];
    }
}
