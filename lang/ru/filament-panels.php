<?php

return [
    'pages' => [
        'dashboard' => [
            'title' => 'Панель управления',
        ],
    ],

    'resources' => [
        'Client' => [
            'label' => 'Клиент',
            'plural_label' => 'Клиенты',
        ],
        'Order' => [
            'label' => 'Заказ',
            'plural_label' => 'Заказы',
        ],
        'Tool' => [
            'label' => 'Инструмент',
            'plural_label' => 'Инструменты',
        ],
        'Repair' => [
            'label' => 'Ремонт',
            'plural_label' => 'Ремонты',
        ],

        'Notification' => [
            'label' => 'Уведомление',
            'plural_label' => 'Уведомления',
        ],
    ],

    'widgets' => [
        'latest_orders' => [
            'title' => 'Последние заказы',
            'description' => 'Список последних заказов',
        ],
        'order_chart' => [
            'title' => 'График заказов',
            'description' => 'Статистика заказов',
        ],
        'stats_overview' => [
            'title' => 'Общая статистика',
            'description' => 'Основные показатели',
        ],

    ],
];
