<x-app-layout title="Ремонт оборудования">
    <!-- Hero секция -->
    <x-page-hero 
        title="Ремонт <span class='text-accent'>оборудования</span>"
        description="Профессиональный ремонт маникюрного, парикмахерского и грумерского оборудования. Диагностика, ремонт и обслуживание вашей техники."
        :breadcrumbs="[
            ['name' => 'Ремонт оборудования', 'href' => route('repair')]
        ]"
    />

    <!-- Прайс -->
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-4xl font-bold mb-12">Ремонт оборудования</h1>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-4">Услуга</th>
                            <th class="text-right py-4">Цена</th>
                            <th class="text-right py-4">Срок</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2">Диагностика оборудования</td>
                            <td class="text-right">Бесплатно</td>
                            <td class="text-right">30 мин</td>
                        </tr>
                        <tr>
                            <td class="py-2">Ремонт машинок для стрижки</td>
                            <td class="text-right">от 1000 ₽</td>
                            <td class="text-right">1-3 дня</td>
                        </tr>
                        <tr>
                            <td class="py-2">Ремонт фенов</td>
                            <td class="text-right">от 800 ₽</td>
                            <td class="text-right">1-2 дня</td>
                        </tr>
                        <tr>
                            <td class="py-2">Ремонт электрических ножниц</td>
                            <td class="text-right">от 1500 ₽</td>
                            <td class="text-right">2-4 дня</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Форма заказа -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Заказать ремонт</h2>

            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-8">
                <p class="font-bold">Доставка бесплатная!</p>
                <p>При заказе ремонта оборудования доставка в обе стороны бесплатная</p>
            </div>

            <form class="space-y-6">
                <div>
                    <label class="block mb-2">Наименование аппарата</label>
                    <input type="text" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block mb-2">Описание проблемы</label>
                    <textarea class="w-full px-4 py-2 border rounded" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block mb-2">Ваше имя</label>
                    <input type="text" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block mb-2">Телефон</label>
                    <input type="tel" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" required>
                    <label>Я ознакомлен с условиями доставки</label>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Заказать ремонт
                </button>
            </form>
        </div>
    </section>
</x-app-layout>
