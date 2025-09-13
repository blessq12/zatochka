<?php

namespace App\Domain\Order;

class FieldLabels
{
    public const LABELS = [
        'id' => 'ID',
        'client_id' => 'Клиент',
        'branch_id' => 'Филиал',
        'manager_id' => 'Менеджер',
        'master_id' => 'Мастер',
        'order_number' => 'Номер заказа',
        'type' => 'Тип услуги',
        'status' => 'Статус',
        'urgency' => 'Срочность',
        'is_paid' => 'Оплачен',
        'paid_at' => 'Дата оплаты',
        'discount_id' => 'Скидка',
        'total_amount' => 'Общая сумма',
        'final_price' => 'Итоговая цена',
        'cost_price' => 'Себестоимость',
        'profit' => 'Прибыль',
        'description' => 'Описание проблемы',
        'notes' => 'Примечания',
        'is_deleted' => 'Удален',
        'created_at' => 'Дата создания',
        'updated_at' => 'Дата обновления',
    ];

    public static function getLabel(string $field): string
    {
        return self::LABELS[$field] ?? $field;
    }

    public static function getLabels(): array
    {
        return self::LABELS;
    }
}
