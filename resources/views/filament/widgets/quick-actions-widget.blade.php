<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Быстрые действия
        </x-slot>

        <div class="flex flex-wrap gap-4">
            <a
                href="{{ $this->getCreateOrderUrl() }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors"
            >
                <x-heroicon-o-plus-circle class="w-5 h-5" />
                Создать заказ
            </a>

            <a
                href="{{ $this->getCreateClientUrl() }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-lg font-medium transition-colors"
            >
                <x-heroicon-o-user-plus class="w-5 h-5" />
                Создать клиента
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
