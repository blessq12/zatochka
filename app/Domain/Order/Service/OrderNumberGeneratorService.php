<?php

namespace App\Domain\Order\Service;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Exception\OrderNumberAlreadyExistsException;

class OrderNumberGeneratorService
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Генерирует уникальный номер заказа
     * Формат: ORD-YYYYMMDD-XXXX
     * где XXXX - последовательный номер от 0001 до 9999
     */
    public function generate(): string
    {
        $datePrefix = date('Ymd');
        $baseNumber = "ORD-{$datePrefix}-";

        for ($attempt = 1; $attempt <= 100; $attempt++) {
            $sequence = str_pad($attempt, 4, '0', STR_PAD_LEFT);
            $orderNumber = $baseNumber . $sequence;

            if (!$this->orderRepository->existsByNumber($orderNumber)) {
                return $orderNumber;
            }
        }

        $timestamp = date('His');
        $fallbackNumber = $baseNumber . $timestamp;

        if ($this->orderRepository->existsByNumber($fallbackNumber)) {
            throw new OrderNumberAlreadyExistsException(
                "Не удалось сгенерировать уникальный номер заказа за {$attempt} попыток"
            );
        }

        return $fallbackNumber;
    }

    /**
     * Генерирует номер заказа с префиксом филиала
     * Формат: ORD-{BRANCH_CODE}-YYYYMMDD-XXXX
     */
    public function generateWithBranch(string $branchCode): string
    {
        $datePrefix = date('Ymd');
        $baseNumber = "ORD-{$branchCode}-{$datePrefix}-";

        for ($attempt = 1; $attempt <= 100; $attempt++) {
            $sequence = str_pad($attempt, 4, '0', STR_PAD_LEFT);
            $orderNumber = $baseNumber . $sequence;

            if (!$this->orderRepository->existsByNumber($orderNumber)) {
                return $orderNumber;
            }
        }

        // Fallback с timestamp
        $timestamp = date('His');
        $fallbackNumber = $baseNumber . $timestamp;

        if ($this->orderRepository->existsByNumber($fallbackNumber)) {
            throw new OrderNumberAlreadyExistsException(
                "Не удалось сгенерировать уникальный номер заказа для филиала {$branchCode}"
            );
        }

        return $fallbackNumber;
    }

    /**
     * Проверяет уникальность номера заказа
     */
    public function isUnique(string $orderNumber): bool
    {
        return !$this->orderRepository->existsByNumber($orderNumber);
    }

    /**
     * Валидирует формат номера заказа
     */
    public function validateFormat(string $orderNumber): bool
    {
        // Проверяем формат ORD-YYYYMMDD-XXXX или ORD-BRANCH-YYYYMMDD-XXXX
        $pattern = '/^ORD-([A-Z0-9]+-)?\d{8}-\d{4}$/';
        return preg_match($pattern, $orderNumber) === 1;
    }
}
