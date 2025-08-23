<x-app-layout title="Пользовательское соглашение">
    <!-- Hero секция -->
    <x-page-hero title="Пользовательское <span class='text-accent'>соглашение</span>"
        description="Правила использования нашего сайта и услуг по заточке и ремонту инструментов." :breadcrumbs="[['name' => 'Пользовательское соглашение', 'href' => route('terms-of-service')]]" />

    <!-- Основной контент -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4">
            <div class="prose prose-lg dark:prose-invert max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">1. Общие положения</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Настоящее Пользовательское соглашение (далее — Соглашение) регулирует отношения между ИП Максим
                    (далее — Исполнитель, мы) и физическими лицами (далее — Заказчики, вы) в связи с оказанием услуг по
                    заточке и ремонту инструментов.
                </p>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Используя наш сайт и заказывая услуги, вы принимаете условия настоящего Соглашения в полном объеме.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">2. Предмет соглашения</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Исполнитель обязуется оказать следующие услуги:</p>
                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-6">
                    <li>Заточка различных видов инструментов</li>
                    <li>Ремонт и восстановление инструментов</li>
                    <li>Консультации по уходу за инструментами</li>
                    <li>Доставка инструментов (при заказе услуги доставки)</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">3. Порядок оформления заказа</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Заказ услуг осуществляется следующим образом:</p>
                <ol class="list-decimal pl-6 text-gray-700 dark:text-gray-300 mb-6">
                    <li>Заполнение формы заказа на сайте</li>
                    <li>Указание контактных данных</li>
                    <li>Описание требуемых работ</li>
                    <li>Согласие с условиями доставки и обработки персональных данных</li>
                    <li>Отправка заявки</li>
                </ol>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">4. Сроки выполнения работ</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Сроки выполнения работ зависят от:</p>
                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-6">
                    <li>Сложности и объема работ</li>
                    <li>Загруженности мастерской</li>
                    <li>Наличия необходимых материалов</li>
                    <li>Выбранной срочности (обычная/срочная)</li>
                </ul>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Точные сроки выполнения работ согласовываются с заказчиком при приеме инструмента.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">5. Стоимость услуг</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Стоимость услуг определяется:</p>
                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-6">
                    <li>Типом и состоянием инструмента</li>
                    <li>Объемом выполняемых работ</li>
                    <li>Срочностью выполнения</li>
                    <li>Необходимостью дополнительных материалов</li>
                </ul>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Окончательная стоимость согласовывается с заказчиком до начала выполнения работ.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">6. Качество услуг</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Исполнитель гарантирует качественное выполнение работ в соответствии с принятыми в отрасли
                    стандартами. При обнаружении недостатков в выполненной работе заказчик имеет право на бесплатное
                    устранение недостатков в разумные сроки.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">7. Ответственность сторон</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Исполнитель несет ответственность за:</p>
                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
                    <li>Качество выполненных работ</li>
                    <li>Соблюдение согласованных сроков</li>
                    <li>Сохранность инструментов во время выполнения работ</li>
                </ul>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Заказчик несет ответственность за предоставление достоверной информации о состоянии инструмента и
                    требованиях к работам.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">8. Условия доставки</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">При заказе услуги доставки:</p>
                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-6">
                    <li>Доставка осуществляется в согласованное время</li>
                    <li>Стоимость доставки оплачивается отдельно</li>
                    <li>При получении инструмента заказчик проверяет его состояние</li>
                    <li>Претензии по качеству принимаются в момент получения</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">9. Конфиденциальность</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Стороны обязуются не разглашать конфиденциальную информацию, полученную в ходе исполнения настоящего
                    Соглашения, без предварительного письменного согласия другой стороны.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">10. Изменение соглашения</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Исполнитель оставляет за собой право вносить изменения в настоящее Соглашение. Изменения вступают в
                    силу с момента их опубликования на сайте. Продолжение использования услуг означает согласие с новыми
                    условиями.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">11. Контактная информация</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    По всем вопросам, связанным с оказанием услуг, вы можете обращаться:
                </p>
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <p class="text-gray-700 dark:text-gray-300 mb-2">
                        <strong>Телефон:</strong> +7 (983) 233-59-07
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 mb-2">
                        <strong>Email:</strong> zatochka.tsk@yandex.ru
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        <strong>Адрес:</strong> Пр. Ленина 169/пер. Карповский 12, Томск
                    </p>
                </div>

                <div
                    class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-blue-800 dark:text-blue-200 text-sm">
                        <strong>Дата последнего обновления:</strong> {{ date('d.m.Y') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
