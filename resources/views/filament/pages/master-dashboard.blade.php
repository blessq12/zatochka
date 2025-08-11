<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Левая панель - список заказов -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Активные заказы</h3>
                    <div class="text-sm text-gray-500">
                        {{ count($orders) }} заказов
                    </div>
                </div>

                <!-- Фильтры -->
                <div class="mb-4 space-y-3">
                    {{ $this->form }}
                </div>

                <!-- Список заказов -->
                <div class="space-y-3 max-h-96 overflow-y-auto" id="orders-list">
                    @foreach ($orders as $order)
                        <div class="order-item p-4 border rounded-lg cursor-pointer transition-colors hover:bg-gray-50 {{ $selectedOrder && $selectedOrder->id === $order->id ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200' }}"
                            wire:click="selectOrder({{ $order->id }})" data-order-id="{{ $order->id }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900">#{{ $order->order_number }}</span>
                                <span
                                    class="px-2 py-1 text-xs rounded-full
                                    @if ($order->status === 'master_received') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'in_work') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'ready') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $order->getStatusOptions()[$order->status] ?? $order->status }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-600">
                                <div>{{ $order->client->name ?? 'Клиент не указан' }}</div>
                                <div>{{ $order->service_type }} - {{ $order->tool_type }}</div>
                                <div>{{ $order->total_tools_count }} инструментов</div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->created_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Правая панель - детали заказа -->
        <div class="lg:col-span-2">
            @if ($selectedOrder)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Заказ #{{ $selectedOrder->order_number }}
                        </h3>
                        <div class="flex space-x-2">
                            @if ($selectedOrder->status === 'master_received')
                                <button
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                    wire:click="updateOrderStatus({{ $selectedOrder->id }}, 'in_work')">
                                    Начать работу
                                </button>
                            @elseif($selectedOrder->status === 'in_work')
                                <button
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                    wire:click="updateOrderStatus({{ $selectedOrder->id }}, 'ready')">
                                    Завершить работу
                                </button>
                            @elseif($selectedOrder->status === 'ready')
                                <button
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                                    wire:click="updateOrderStatus({{ $selectedOrder->id }}, 'courier_delivery')">
                                    Передать курьеру
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Информация о заказе -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Информация о заказе</h4>
                            <div class="space-y-2 text-sm">
                                <div><span class="font-medium">Тип услуги:</span> {{ $selectedOrder->service_type }}
                                </div>
                                <div><span class="font-medium">Тип инструмента:</span> {{ $selectedOrder->tool_type }}
                                </div>
                                <div><span class="font-medium">Количество:</span>
                                    {{ $selectedOrder->total_tools_count }}</div>
                                <div><span class="font-medium">Дата поступления:</span>
                                    {{ $selectedOrder->created_at->format('d.m.Y H:i') }}</div>
                                <div><span class="font-medium">Сумма:</span>
                                    {{ number_format($selectedOrder->total_amount, 0) }} ₽</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Информация о клиенте</h4>
                            <div class="space-y-2 text-sm">
                                <div><span class="font-medium">Имя:</span>
                                    {{ $selectedOrder->client->name ?? 'Не указано' }}</div>
                                <div><span class="font-medium">Телефон:</span>
                                    {{ $selectedOrder->client->phone ?? 'Не указано' }}</div>
                                <div><span class="font-medium">Email:</span>
                                    {{ $selectedOrder->client->email ?? 'Не указано' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Описание проблемы -->
                    @if ($selectedOrder->problem_description)
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-900 mb-3">Описание проблемы</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                {{ $selectedOrder->problem_description }}
                            </div>
                        </div>
                    @endif

                    <!-- История заказов клиента -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">История заказов клиента</h4>
                        <div class="bg-gray-50 p-4 rounded-lg max-h-40 overflow-y-auto">
                            @php
                                $clientOrders = $selectedOrder->client
                                    ->orders()
                                    ->where('id', '!=', $selectedOrder->id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp

                            @if ($clientOrders->count() > 0)
                                @foreach ($clientOrders as $order)
                                    <div class="border-b border-gray-200 pb-2 mb-2 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-medium">#{{ $order->order_number }}</div>
                                                <div class="text-sm text-gray-600">{{ $order->service_type }} -
                                                    {{ $order->tool_type }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-500">
                                                    {{ $order->created_at->format('d.m.Y') }}</div>
                                                <div class="text-sm font-medium">
                                                    {{ number_format($order->total_amount, 0) }} ₽</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-gray-500 text-sm">История заказов отсутствует</div>
                            @endif
                        </div>
                    </div>

                    <!-- Форма для описания работы -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Описание выполненной работы</h4>
                            <div id="work-description-form" class="space-y-4">
                                <!-- Описание работы -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Что было сделано</label>
                                    <textarea v-model="workDescription"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                        rows="3" placeholder="Опишите выполненную работу..."></textarea>
                                </div>

                                <!-- Использованные материалы -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Расходные
                                        материалы</label>
                                    <div class="space-y-2">
                                        <div v-for="material in availableMaterials" :key="material.id"
                                            class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <span class="text-sm">@{{ material.name }}</span>
                                            <button @click="addMaterial(material)"
                                                class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                Добавить
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="usedMaterials.length > 0" class="mt-3">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Использовано:</div>
                                        <div class="space-y-1">
                                            <div v-for="(material, index) in usedMaterials" :key="index"
                                                class="flex items-center justify-between p-2 bg-green-50 rounded">
                                                <span class="text-sm">@{{ material.name }} - @{{ material.cost }}
                                                    ₽</span>
                                                <button @click="removeMaterial(index)"
                                                    class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                                    Удалить
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">
                                            Стоимость материалов: <span class="font-medium">@{{ totalMaterialCost }}
                                                ₽</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Кнопка сохранения -->
                                <div class="pt-4">
                                    <button @click="saveWorkDescription"
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                        Сохранить описание работы
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Поле для скидки -->
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-semibold text-yellow-800">ВАЖНО: Не забудьте указать скидку!</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-yellow-700 mb-1">Скидка (%)</label>
                                    <input type="number" min="0" max="100"
                                        class="w-full px-3 py-2 border border-yellow-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white"
                                        placeholder="0" id="discount-percent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-yellow-700 mb-1">Скидка (₽)</label>
                                    <input type="number" min="0"
                                        class="w-full px-3 py-2 border border-yellow-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white"
                                        placeholder="0" id="discount-amount">
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-yellow-700">
                                <span id="final-price-display">Итоговая сумма: <span
                                        class="font-semibold">{{ number_format($selectedOrder->total_amount, 0) }}
                                        ₽</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Выберите заказ</h3>
                        <p class="text-gray-500">Выберите заказ из списка слева для просмотра деталей</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        <script>
            // Vue компонент для интерактивности
            const {
                createApp
            } = Vue;

            createApp({
                data() {
                    return {
                        workDescription: '',
                        usedMaterials: [],
                        discountPercent: 0,
                        discountAmount: 0,
                        originalPrice: {{ $selectedOrder ? $selectedOrder->total_amount : 0 }},
                        availableMaterials: [{
                                id: 1,
                                name: 'Абразивный круг',
                                cost: 150
                            },
                            {
                                id: 2,
                                name: 'Полировальная паста',
                                cost: 80
                            },
                            {
                                id: 3,
                                name: 'Смазка',
                                cost: 120
                            },
                            {
                                id: 4,
                                name: 'Запчасти',
                                cost: 200
                            }
                        ]
                    }
                },
                computed: {
                    totalMaterialCost() {
                        return this.usedMaterials.reduce((sum, material) => sum + material.cost, 0);
                    },
                    finalPrice() {
                        const basePrice = this.originalPrice + this.totalMaterialCost;
                        const percentDiscount = (basePrice * this.discountPercent) / 100;
                        const totalDiscount = Math.max(percentDiscount, this.discountAmount);
                        return Math.max(0, basePrice - totalDiscount);
                    }
                },
                methods: {
                    addMaterial(material) {
                        this.usedMaterials.push({
                            ...material
                        });
                    },
                    removeMaterial(index) {
                        this.usedMaterials.splice(index, 1);
                    },
                    updateDiscountPercent() {
                        const percent = parseFloat(document.getElementById('discount-percent').value) || 0;
                        this.discountPercent = percent;
                        this.updateFinalPrice();
                    },
                    updateDiscountAmount() {
                        const amount = parseFloat(document.getElementById('discount-amount').value) || 0;
                        this.discountAmount = amount;
                        this.updateFinalPrice();
                    },
                    updateFinalPrice() {
                        const display = document.getElementById('final-price-display');
                        if (display) {
                            display.innerHTML =
                                `Итоговая сумма: <span class="font-semibold">${this.finalPrice.toLocaleString()} ₽</span>`;
                        }
                    },
                    saveWorkDescription() {
                        const data = {
                            workDescription: this.workDescription,
                            usedMaterials: this.usedMaterials,
                            discountPercent: this.discountPercent,
                            discountAmount: this.discountAmount,
                            finalPrice: this.finalPrice
                        };
                        console.log('Данные для сохранения:', data);

                        // Отправляем данные на сервер через Livewire
                        @this.call('saveWorkData', data);
                    }
                },
                mounted() {
                    // Слушаем события от Livewire
                    window.addEventListener('orderSelected', (event) => {
                        console.log('Выбран заказ:', event.detail.orderId);
                        // Сброс формы при выборе нового заказа
                        this.workDescription = '';
                        this.usedMaterials = [];
                        this.discountPercent = 0;
                        this.discountAmount = 0;
                    });

                    // Привязываем обработчики к полям скидки
                    document.addEventListener('DOMContentLoaded', () => {
                        const percentInput = document.getElementById('discount-percent');
                        const amountInput = document.getElementById('discount-amount');

                        if (percentInput) {
                            percentInput.addEventListener('input', () => this.updateDiscountPercent());
                        }
                        if (amountInput) {
                            amountInput.addEventListener('input', () => this.updateDiscountAmount());
                        }
                    });
                }
            }).mount('#work-description-form');
        </script>
    @endpush
</x-filament-panels::page>
