<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="space-y-4">
        @php
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $dayLabels = [
                'monday' => 'Понедельник',
                'tuesday' => 'Вторник',
                'wednesday' => 'Среда',
                'thursday' => 'Четверг',
                'friday' => 'Пятница',
                'saturday' => 'Суббота',
                'sunday' => 'Воскресенье',
            ];
        @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">День
                            недели</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Рабочий день</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время
                            начала</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время
                            окончания</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Примечание</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($days as $day)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $dayLabels[$day] }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="checkbox" id="working_{{ $day }}"
                                    name="{{ $getStatePath() }}[{{ $day }}][is_working]" value="1"
                                    @checked($getState()[$day]['is_working'] ?? false)
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="time" name="{{ $getStatePath() }}[{{ $day }}][start]"
                                    value="{{ $getState()[$day]['start'] ?? '' }}"
                                    :disabled="!document.getElementById('working_{{ $day }}').checked"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="time" name="{{ $getStatePath() }}[{{ $day }}][end]"
                                    value="{{ $getState()[$day]['end'] ?? '' }}"
                                    :disabled="!document.getElementById('working_{{ $day }}').checked"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <input type="text" name="{{ $getStatePath() }}[{{ $day }}][note]"
                                    value="{{ $getState()[$day]['note'] ?? '' }}" placeholder="Например: Рабочий день"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработчик изменения чекбокса "Рабочий день"
            document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const timeInputs = row.querySelectorAll('input[type="time"]');
                    const isWorking = this.checked;

                    timeInputs.forEach(function(input) {
                        input.disabled = !isWorking;
                        if (!isWorking) {
                            input.value = '';
                        }
                    });
                });
            });
        });
    </script>
</x-dynamic-component>
