<?php

namespace App\Services\Document\Contracts;

use App\Models\Order;

interface DocumentGeneratorInterface
{
    /**
     * Генерирует PDF документ для заказа
     *
     * @param Order $order
     * @return string Путь к сгенерированному PDF файлу
     */
    public function generate(Order $order): string;

    /**
     * Возвращает название документа
     *
     * @return string
     */
    public function getDocumentName(): string;

    /**
     * Возвращает имя файла для документа
     *
     * @param Order $order
     * @return string
     */
    public function getFileName(Order $order): string;
}
