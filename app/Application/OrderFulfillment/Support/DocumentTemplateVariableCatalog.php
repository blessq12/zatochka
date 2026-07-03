<?php

namespace App\Application\OrderFulfillment\Support;

final class DocumentTemplateVariableCatalog
{
    /**
     * @return list<array{key: string, label: string, description: string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'order.number', 'label' => 'Номер заказа', 'description' => 'Номер заказа'],
            ['key' => 'order.date', 'label' => 'Дата заказа', 'description' => 'Дата создания заказа'],
            ['key' => 'order.price', 'label' => 'Стоимость', 'description' => 'Предварительная стоимость, форматированная'],
            ['key' => 'document.title', 'label' => 'Заголовок документа', 'description' => 'Название типа документа'],
            ['key' => 'document.date', 'label' => 'Дата документа', 'description' => 'Текущая дата и время'],
            ['key' => 'service.type', 'label' => 'Тип услуги', 'description' => 'Вид услуги'],
            ['key' => 'service.type_with_urgency', 'label' => 'Услуга со срочностью', 'description' => 'Тип услуги с бейджем «СРОЧНО»'],
            ['key' => 'branch.name', 'label' => 'Филиал', 'description' => 'Название филиала'],
            ['key' => 'branch.address', 'label' => 'Адрес филиала', 'description' => 'Адрес филиала'],
            ['key' => 'branch.phone', 'label' => 'Телефон филиала', 'description' => 'Телефон филиала'],
            ['key' => 'client.name', 'label' => 'ФИО клиента', 'description' => 'Имя клиента'],
            ['key' => 'client.phone', 'label' => 'Телефон клиента', 'description' => 'Телефон клиента'],
            ['key' => 'equipment.name', 'label' => 'Оборудование', 'description' => 'Название оборудования'],
            ['key' => 'problem.description', 'label' => 'Описание проблемы', 'description' => 'Текст проблемы'],
            ['key' => 'manager.name', 'label' => 'Менеджер', 'description' => 'ФИО менеджера'],
            ['key' => 'master.name', 'label' => 'Мастер', 'description' => 'ФИО мастера'],
            ['key' => 'company.header', 'label' => 'Реквизиты компании', 'description' => 'Блок реквизитов одной строкой'],
            ['key' => 'logo', 'label' => 'Логотип', 'description' => 'HTML-изображение логотипа'],
            ['key' => 'items.section', 'label' => 'Предметы приёма', 'description' => 'Таблица оборудования и инструментов'],
            ['key' => 'problem.section', 'label' => 'Блок проблемы', 'description' => 'Секция описания проблемы'],
            ['key' => 'price.section', 'label' => 'Блок стоимости', 'description' => 'Секция предварительной стоимости'],
            ['key' => 'works.table', 'label' => 'Таблица работ', 'description' => 'Выполненные работы с итогом'],
            ['key' => 'materials.table', 'label' => 'Таблица материалов', 'description' => 'Материалы с итогом'],
            ['key' => 'master.section', 'label' => 'Блок мастера', 'description' => 'Секция исполнителя'],
            ['key' => 'tools.list', 'label' => 'Список инструментов', 'description' => 'Инструменты текстом'],
        ];
    }

    /**
     * @return list<array{key: string, label: string, fields: list<string>}>
     */
    public static function loops(): array
    {
        return [
            [
                'key' => 'tools',
                'label' => 'Инструменты',
                'fields' => ['type', 'quantity'],
            ],
            [
                'key' => 'works',
                'label' => 'Работы',
                'fields' => ['description', 'price', 'price_formatted'],
            ],
            [
                'key' => 'materials',
                'label' => 'Материалы',
                'fields' => ['name', 'quantity', 'price', 'price_formatted'],
            ],
        ];
    }
}
