<x-filament::page>
    <div class="space-y-6">
        <form wire:submit="onFilterSubmit">
            {{ $this->form }}
        </form>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <x-filament::card>
                <div class="flex flex-col items-center justify-center">
                    <span class="text-sm font-medium text-gray-500">Всего заказов</span>
                    <span class="mt-2 text-3xl font-bold">{{ $data['total_orders'] ?? 0 }}</span>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex flex-col items-center justify-center">
                    <span class="text-sm font-medium text-gray-500">Общая выручка</span>
                    <span class="mt-2 text-3xl font-bold">{{ number_format($data['total_revenue'] ?? 0, 0, '.', ' ') }}
                        ₽</span>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex flex-col items-center justify-center">
                    <span class="text-sm font-medium text-gray-500">Общая прибыль</span>
                    <span class="mt-2 text-3xl font-bold">{{ number_format($data['total_profit'] ?? 0, 0, '.', ' ') }}
                        ₽</span>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex flex-col items-center justify-center">
                    <span class="text-sm font-medium text-gray-500">Средний чек</span>
                    <span class="mt-2 text-3xl font-bold">{{ number_format($data['average_check'] ?? 0, 0, '.', ' ') }}
                        ₽</span>
                </div>
            </x-filament::card>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-filament::card>
                <div class="p-4">
                    <h3 class="text-lg font-medium">Популярные инструменты</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($data['popular_tools'] ?? [] as $tool)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ $tool->name }}</span>
                                <span class="text-sm font-medium">{{ $tool->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="p-4">
                    <h3 class="text-lg font-medium">Популярные виды ремонта</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($data['popular_repairs'] ?? [] as $repair)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ $repair->description }}</span>
                                <span class="text-sm font-medium">{{ $repair->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-filament::card>
        </div>
    </div>
</x-filament::page>
