<?php

namespace App\Infrastructure\SiteContent\Fixture;

/**
 * Стартовый контент для сидера. Не используется как runtime Repository.
 */
final class SiteContentFixture
{
    /**
     * @return array<string, mixed>
     */
    public static function bootstrap(): array
    {
        return [
            'company' => [
                'name' => 'Заточка.ТСК',
                'tagline' => 'Профессиональная заточка инструментов',
                'owner_name' => 'ИП Пример Пример Провеевич',
                'inn' => '700000000000',
                'ogrn' => '300000000000000',
                'legal_address' => 'г. Томск, ул. Примерная, 1',
                'actual_address' => 'г. Томск, ул. Примерная, 1',
            ],
            'contacts' => [
                'phone' => '+7 (3822) 00-00-00',
                'email' => 'info@zatochka.tsk',
                'contact_person' => 'Администратор',
                'address' => [
                    'main' => 'г. Томск, ул. Примерная, 1',
                    'directions' => "Вход со двора.\nОриентир — вывеска «Заточка.ТСК».",
                ],
                'social' => [
                    'email' => 'info@zatochka.tsk',
                    'links' => [
                        ['name' => 'WhatsApp', 'url' => 'https://wa.me/73822000000'],
                        ['name' => 'Telegram', 'url' => 'https://t.me/zatochka_tsk'],
                    ],
                ],
            ],
            'schedule' => [
                'days' => [
                    ['label' => 'Пн–Пт', 'hours' => '10:00–19:00'],
                    ['label' => 'Сб', 'hours' => '11:00–16:00'],
                    ['label' => 'Вс', 'hours' => 'Выходной'],
                ],
            ],
            'faq' => [
                'items' => [
                    [
                        'question' => 'Сколько длится заточка?',
                        'answer_lines' => [
                            'Обычно 1–2 рабочих дня.',
                            'Срочный заказ обсуждается отдельно.',
                        ],
                    ],
                    [
                        'question' => 'Можно ли с доставкой?',
                        'answer_lines' => [
                            'Да, курьер забирает и возвращает инструмент.',
                        ],
                    ],
                ],
            ],
            'delivery_info' => [
                'free_conditions' => [
                    'Бесплатно при заказе от 3000 ₽ в пределах города.',
                    'Забор и возврат в согласованный интервал.',
                ],
                'advantages' => [
                    [
                        'title' => 'Забор и возврат',
                        'text' => 'Не нужно ехать в мастерскую дважды.',
                    ],
                    [
                        'title' => 'Фиксация заявки',
                        'text' => 'Доставка отмечается прямо в форме заказа.',
                    ],
                ],
            ],
            'prices' => [
                [
                    'category' => 'sharpening',
                    'name' => 'Маникюрный инструмент',
                    'description' => 'Комплект',
                    'price' => '500',
                    'prefix' => 'from',
                ],
                [
                    'category' => 'sharpening',
                    'name' => 'Парикмахерские ножницы',
                    'description' => null,
                    'price' => '700',
                    'prefix' => null,
                ],
                [
                    'category' => 'repair',
                    'name' => 'Диагностика оборудования',
                    'description' => 'С оценкой ремонта',
                    'price' => '0',
                    'prefix' => null,
                ],
                [
                    'category' => 'repair',
                    'name' => 'Ремонт фрезера',
                    'description' => null,
                    'price' => '1500',
                    'prefix' => 'from',
                ],
            ],
        ];
    }

    /**
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}|null
     */
    public static function legalDocumentBySlug(string $slug): ?array
    {
        $documents = [
            'privacy-policy' => [
                'type' => 'privacy_policy',
                'slug' => 'privacy-policy',
                'title' => 'Политика конфиденциальности',
                'body_html' => '<p>Текст политики конфиденциальности (mock).</p>',
                'updated_at' => '2026-01-01T00:00:00+00:00',
            ],
            'user-agreement' => [
                'type' => 'user_agreement',
                'slug' => 'user-agreement',
                'title' => 'Пользовательское соглашение',
                'body_html' => '<p>Текст пользовательского соглашения (mock).</p>',
                'updated_at' => '2026-01-01T00:00:00+00:00',
            ],
            'usage-rules' => [
                'type' => 'usage_rules',
                'slug' => 'usage-rules',
                'title' => 'Правила использования',
                'body_html' => '<p>Текст правил использования (mock).</p>',
                'updated_at' => '2026-01-01T00:00:00+00:00',
            ],
        ];

        return $documents[$slug] ?? null;
    }
}
