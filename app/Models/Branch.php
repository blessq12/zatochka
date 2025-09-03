<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'working_hours',
        'working_schedule',
        'opening_time',
        'closing_time',
        'latitude',
        'longitude',
        'description',
        'additional_data',
        'is_active',
        'is_main',
        'sort_order',
        'is_deleted',
    ];

    protected $casts = [
        'working_schedule' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_main' => 'boolean',
        'sort_order' => 'integer',
        'is_deleted' => 'boolean',
        'additional_data' => 'array',
    ];

    // Связи
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }

    // Scope для активных филиалов
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false)->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Методы для работы с расписанием
    public function getWorkingSchedule(): array
    {
        if ($this->working_schedule && is_array($this->working_schedule)) {
            return $this->working_schedule;
        }

        // Fallback на старое поле working_hours
        if ($this->working_hours) {
            return $this->parseLegacyWorkingHours($this->working_hours);
        }

        return $this->getDefaultWorkingSchedule();
    }

    public function setWorkingSchedule(array $schedule): void
    {
        $this->update(['working_schedule' => $schedule]);
    }

    public function isWorkingToday(): bool
    {
        $today = strtolower(now()->format('l'));
        $schedule = $this->getWorkingSchedule();

        return $schedule[$today]['is_working'] ?? false;
    }

    public function isWorkingNow(): bool
    {
        if (!$this->isWorkingToday()) {
            return false;
        }

        $now = now();
        $today = strtolower($now->format('l'));
        $schedule = $this->getWorkingSchedule();

        if (!isset($schedule[$today]) || !$schedule[$today]['is_working']) {
            return false;
        }

        $startTime = $schedule[$today]['start'] ?? null;
        $endTime = $schedule[$today]['end'] ?? null;

        if (!$startTime || !$endTime) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    public function getWorkingHoursForDay(string $day): ?array
    {
        $schedule = $this->getWorkingSchedule();
        $day = strtolower($day);

        return $schedule[$day] ?? null;
    }

    public function getNextWorkingDay(): ?string
    {
        $schedule = $this->getWorkingSchedule();
        $today = now();

        for ($i = 1; $i <= 7; $i++) {
            $nextDay = $today->copy()->addDays($i);
            $dayName = strtolower($nextDay->format('l'));

            if (isset($schedule[$dayName]) && $schedule[$dayName]['is_working']) {
                return $dayName;
            }
        }

        return null;
    }

    // Методы для работы со статусом
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function setAsMain()
    {
        // Снимаем флаг главного с других филиалов компании
        $this->company->branches()->where('is_main', true)->update(['is_main' => false]);
        $this->update(['is_main' => true]);
    }

    public function markDeleted()
    {
        $this->update(['is_deleted' => true, 'is_active' => false]);
    }

    // Проверки статуса
    public function isMain(): bool
    {
        return $this->is_main;
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->is_deleted;
    }

    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    // Приватные методы
    private function parseLegacyWorkingHours(string $workingHours): array
    {
        // Парсим строку вида "Пн-Пт: 10:00-19:00, Сб: 11:00-16:00"
        $schedule = $this->getDefaultWorkingSchedule();

        if (preg_match('/Пн-Пт:\s*(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $workingHours, $matches)) {
            $start = $matches[1];
            $end = $matches[2];

            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
                $schedule[$day] = [
                    'is_working' => true,
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        if (preg_match('/Сб:\s*(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $workingHours, $matches)) {
            $schedule['saturday'] = [
                'is_working' => true,
                'start' => $matches[1],
                'end' => $matches[2]
            ];
        }

        return $schedule;
    }

    private function getDefaultWorkingSchedule(): array
    {
        return [
            'monday' => ['is_working' => false, 'start' => null, 'end' => null],
            'tuesday' => ['is_working' => false, 'start' => null, 'end' => null],
            'wednesday' => ['is_working' => false, 'start' => null, 'end' => null],
            'thursday' => ['is_working' => false, 'start' => null, 'end' => null],
            'friday' => ['is_working' => false, 'start' => null, 'end' => null],
            'saturday' => ['is_working' => false, 'start' => null, 'end' => null],
            'sunday' => ['is_working' => false, 'start' => null, 'end' => null],
        ];
    }
}
