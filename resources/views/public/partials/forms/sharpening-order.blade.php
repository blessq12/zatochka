@php
    /** @var list<array{value: string, label: string}> $toolTypes */
    $inputClass = 'w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20';
@endphp
<section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
    <div class="max-w-2xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
        <div class="mb-8">
            <h2 class="text-2xl sm:text-3xl font-jost-bold text-[#C3006B] text-center mb-4">
                ОФОРМИТЬ ЗАКАЗ
            </h2>
            <div class="h-px bg-dark-blue-500 dark:bg-dark-blue-300"></div>
        </div>

        <form data-public-order-form data-service-type="sharpening" class="space-y-6">
            <p data-form-error class="hidden bg-red-50 border border-red-300 text-red-700 px-6 py-4 dark:bg-red-900/30 dark:text-red-400"></p>
            <p data-form-status class="hidden bg-green-50 border border-green-300 text-green-800 px-6 py-4 dark:bg-green-900/30 dark:text-green-300"></p>

            <div>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">
                    Количество инструментов <span class="text-red-500">*</span>
                </label>
                <input name="tools_count" type="number" min="1" required class="{{ $inputClass }}" placeholder="Введите количество">
            </div>

            <div>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">
                    Тип инструментов <span class="text-red-500">*</span>
                </label>
                <select name="tool_type" required class="{{ $inputClass }}">
                    <option value="" disabled selected>Выберите тип инструментов</option>
                    @foreach($toolTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3">
                <input name="needs_delivery" type="checkbox" id="sharpening_needs_delivery" checked class="w-5 h-5">
                <label for="sharpening_needs_delivery" class="text-base font-jost-medium text-dark-gray-500 dark:text-gray-200">
                    Нужна доставка (заберём у вас и вернём обратно)
                </label>
            </div>

            <div data-delivery-address>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">Адрес доставки</label>
                <input name="delivery_address" type="text" class="{{ $inputClass }}" placeholder="Введите адрес">
            </div>

            <div>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">Ваше имя <span class="text-red-500">*</span></label>
                <input name="name" type="text" required class="{{ $inputClass }}" placeholder="Введите ваше имя">
            </div>

            <div>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">Телефон <span class="text-red-500">*</span></label>
                <input name="phone" type="tel" required class="{{ $inputClass }}" placeholder="+7 (___) ___-__-__">
            </div>

            <div>
                <label class="block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2">Комментарий</label>
                <textarea name="comment" rows="3" class="{{ $inputClass }}" placeholder="Дополнительная информация"></textarea>
            </div>

            <div class="space-y-3">
                <label class="flex items-start gap-3 text-sm text-dark-gray-500 dark:text-gray-300">
                    <input name="privacy_agreement" type="checkbox" required class="mt-1 w-5 h-5">
                    <span>Согласен на обработку персональных данных (<a href="{{ url('/privacy-policy') }}" class="underline text-[#C3006B]">политика</a>)</span>
                </label>
                <label class="flex items-start gap-3 text-sm text-dark-gray-500 dark:text-gray-300">
                    <input name="delivery_agreement" type="checkbox" required class="mt-1 w-5 h-5">
                    <span>Согласен с <a href="{{ url('/delivery') }}" class="underline text-[#C3006B]">условиями доставки</a></span>
                </label>
            </div>

            <button type="submit" class="w-full bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-8 py-4 font-jost-bold text-lg">
                Отправить заявку
            </button>
        </form>
    </div>
</section>
