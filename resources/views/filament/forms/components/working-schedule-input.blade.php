<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ 
        schedule: $wire.{{ $applyStateBindingModifiers('$entangle(\'' . $getStatePath() . '\')') }},
        days: {
            monday: 'Понедельник',
            tuesday: 'Вторник',
            wednesday: 'Среда',
            thursday: 'Четверг',
            friday: 'Пятница',
            saturday: 'Суббота',
            sunday: 'Воскресенье'
        },
        init() {
            if (!this.schedule || typeof this.schedule === 'string') {
                this.schedule = this.getDefaultSchedule();
            }
        },
        getDefaultSchedule() {
            return {
                monday: { is_working: true, start: '09:00', end: '18:00', note: 'Рабочий день' },
                tuesday: { is_working: true, start: '09:00', end: '18:00', note: 'Рабочий день' },
                wednesday: { is_working: true, start: '09:00', end: '18:00', note: 'Рабочий день' },
                thursday: { is_working: true, start: '09:00', end: '18:00', note: 'Рабочий день' },
                friday: { is_working: true, start: '09:00', end: '18:00', note: 'Рабочий день' },
                saturday: { is_working: false, start: null, end: null, note: 'Выходной' },
                sunday: { is_working: false, start: null, end: null, note: 'Выходной' }
            };
        }
    }" class="space-y-4">
        <template x-for="(day, dayKey) in days" :key="dayKey">
            <div class="border border-gray-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="font-medium text-gray-700" x-text="day"></label>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            x-model="schedule[dayKey].is_working"
                            @change="if (!schedule[dayKey].is_working) { schedule[dayKey].start = null; schedule[dayKey].end = null; }"
                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        >
                        <span class="ml-2 text-sm text-gray-600">Рабочий день</span>
                    </label>
                </div>
                <div x-show="schedule[dayKey].is_working" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Время начала</label>
                        <input 
                            type="time" 
                            x-model="schedule[dayKey].start"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Время окончания</label>
                        <input 
                            type="time" 
                            x-model="schedule[dayKey].end"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                    </div>
                </div>
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Примечание</label>
                    <input 
                        type="text" 
                        x-model="schedule[dayKey].note"
                        placeholder="Рабочий день / Выходной"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>