<x-app-layout title="Заточка инструментов">
    <!-- Прайс -->
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-4xl font-bold mb-12">Прайс на заточку</h1>

            <!-- Маникюрные инструменты -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Маникюрные инструменты</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left py-4">Услуга</th>
                                <th class="text-right py-4">Цена</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2">Кусачки</td>
                                <td class="text-right">от 500 ₽</td>
                            </tr>
                            <tr>
                                <td class="py-2">Ножницы</td>
                                <td class="text-right">от 600 ₽</td>
                            </tr>
                            <tr>
                                <td class="py-2">Щипчики</td>
                                <td class="text-right">от 400 ₽</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Парикмахерские инструменты -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Парикмахерские инструменты</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left py-4">Услуга</th>
                                <th class="text-right py-4">Цена</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2">Прямые ножницы</td>
                                <td class="text-right">от 800 ₽</td>
                            </tr>
                            <tr>
                                <td class="py-2">Филировочные ножницы</td>
                                <td class="text-right">от 1000 ₽</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Груминг инструменты -->
            <div>
                <h2 class="text-2xl font-bold mb-6">Груминг инструменты</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left py-4">Услуга</th>
                                <th class="text-right py-4">Цена</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2">Ножницы для груминга</td>
                                <td class="text-right">от 700 ₽</td>
                            </tr>
                            <tr>
                                <td class="py-2">Машинки</td>
                                <td class="text-right">от 900 ₽</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Форма заказа -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Заказать заточку</h2>
            <form class="space-y-6">
                <div>
                    <label class="block mb-2">Количество инструментов</label>
                    <input type="number" min="1" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block mb-2">Ваше имя</label>
                    <input type="text" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block mb-2">Телефон</label>
                    <input type="tel" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block mb-2">Комментарий</label>
                    <textarea class="w-full px-4 py-2 border rounded" rows="4"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" required>
                    <label>Я ознакомлен с условиями доставки</label>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Заказать доставку
                </button>
            </form>
        </div>
    </section>
</x-app-layout>
