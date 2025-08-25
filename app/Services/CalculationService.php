<?php

namespace App\Services;

use App\Models\ServiceType;
use App\Models\DeliveryType;
use Illuminate\Support\Facades\Cache;

class CalculationService
{
    /**
     * Рассчитать стоимость заточки
     */
    public function calculateSharpeningPrice(string $toolType, int $toolsCount, bool $needsDelivery = false): array
    {
        $basePrice = $this->getSharpeningBasePrice($toolType);
        $subtotal = $basePrice * $toolsCount;

        $deliveryCost = 0;
        if ($needsDelivery) {
            $deliveryCost = $this->getDeliveryCost();
        }

        $total = $subtotal + $deliveryCost;

        return [
            'base_price' => $basePrice,
            'tools_count' => $toolsCount,
            'subtotal' => $subtotal,
            'delivery_cost' => $deliveryCost,
            'total' => $total,
            'breakdown' => [
                'sharpening' => $subtotal,
                'delivery' => $deliveryCost,
            ]
        ];
    }

    /**
     * Рассчитать стоимость ремонта
     */
    public function calculateRepairPrice(string $equipmentType, string $problemDescription, bool $needsDelivery = false): array
    {
        $basePrice = $this->getRepairBasePrice($equipmentType);
        $complexityMultiplier = $this->getComplexityMultiplier($problemDescription);

        $subtotal = $basePrice * $complexityMultiplier;

        $deliveryCost = 0;
        if ($needsDelivery) {
            $deliveryCost = $this->getDeliveryCost();
        }

        $total = $subtotal + $deliveryCost;

        return [
            'base_price' => $basePrice,
            'complexity_multiplier' => $complexityMultiplier,
            'subtotal' => $subtotal,
            'delivery_cost' => $deliveryCost,
            'total' => $total,
            'breakdown' => [
                'repair' => $subtotal,
                'delivery' => $deliveryCost,
            ]
        ];
    }

    /**
     * Рассчитать стоимость с учетом скидки
     */
    public function calculateWithDiscount(float $total, float $discountPercent): array
    {
        $discountAmount = ($total * $discountPercent) / 100;
        $finalPrice = $total - $discountAmount;

        return [
            'original_price' => $total,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
        ];
    }

    /**
     * Получить базовую цену заточки
     */
    private function getSharpeningBasePrice(string $toolType): float
    {
        return Cache::remember("sharpening_price_{$toolType}", 3600, function () use ($toolType) {
            $prices = [
                'manicure' => 500,
                'hair' => 800,
                'grooming' => 700,
                'kitchen' => 600,
                'garden' => 650,
                'other' => 600,
            ];

            return $prices[$toolType] ?? 600;
        });
    }

    /**
     * Получить базовую цену ремонта
     */
    private function getRepairBasePrice(string $equipmentType): float
    {
        return Cache::remember("repair_price_{$equipmentType}", 3600, function () use ($equipmentType) {
            $prices = [
                'manicure' => 1000,
                'hair' => 1200,
                'grooming' => 1100,
                'kitchen' => 800,
                'garden' => 900,
                'other' => 1000,
            ];

            return $prices[$equipmentType] ?? 1000;
        });
    }

    /**
     * Получить множитель сложности ремонта
     */
    private function getComplexityMultiplier(string $problemDescription): float
    {
        $keywords = [
            'сложный' => 1.5,
            'трудный' => 1.4,
            'замена' => 1.3,
            'ремонт' => 1.0,
            'простой' => 0.8,
            'легкий' => 0.9,
            'настройка' => 0.7,
        ];

        foreach ($keywords as $keyword => $value) {
            if (mb_stripos($problemDescription, $keyword, 0, 'UTF-8') !== false) {
                return $value;
            }
        }

        return 1.0; // По умолчанию
    }

    /**
     * Получить стоимость доставки
     */
    private function getDeliveryCost(): float
    {
        return Cache::remember('delivery_cost', 3600, function () {
            return 300.0; // Базовая стоимость доставки
        });
    }

    /**
     * Обновить цены в кеше
     */
    public function updatePrices(array $sharpeningPrices = [], array $repairPrices = []): void
    {
        // Обновляем цены заточки
        foreach ($sharpeningPrices as $toolType => $price) {
            Cache::put("sharpening_price_{$toolType}", $price, 3600);
        }

        // Обновляем цены ремонта
        foreach ($repairPrices as $equipmentType => $price) {
            Cache::put("repair_price_{$equipmentType}", $price, 3600);
        }

        // Очищаем общий кеш цен
        Cache::forget('delivery_cost');
    }

    /**
     * Получить все текущие цены
     */
    public function getAllPrices(): array
    {
        $toolTypes = ['manicure', 'hair', 'grooming', 'kitchen', 'garden', 'other'];
        $equipmentTypes = ['manicure', 'hair', 'grooming', 'kitchen', 'garden', 'other'];

        $sharpeningPrices = [];
        foreach ($toolTypes as $toolType) {
            $sharpeningPrices[$toolType] = $this->getSharpeningBasePrice($toolType);
        }

        $repairPrices = [];
        foreach ($equipmentTypes as $equipmentType) {
            $repairPrices[$equipmentType] = $this->getRepairBasePrice($equipmentType);
        }

        return [
            'sharpening' => $sharpeningPrices,
            'repair' => $repairPrices,
            'delivery' => $this->getDeliveryCost(),
        ];
    }
}
