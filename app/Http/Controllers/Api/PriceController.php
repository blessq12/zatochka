<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Получить прайс-лист для заточки
     */
    public function sharpening(): JsonResponse
    {
        $priceItems = PriceItem::active()
            ->byServiceType(PriceItem::TYPE_SHARPENING)
            ->ordered()
            ->get();

        $priceBlocks = $this->groupByCategory($priceItems);

        return response()->json([
            'priceBlocks' => $priceBlocks,
        ]);
    }

    /**
     * Получить прайс-лист для ремонта
     */
    public function repair(): JsonResponse
    {
        $priceItems = PriceItem::active()
            ->byServiceType(PriceItem::TYPE_REPAIR)
            ->ordered()
            ->get();

        $priceBlocks = $this->groupByCategory($priceItems);

        return response()->json([
            'priceBlocks' => $priceBlocks,
        ]);
    }

    /**
     * Получить все прайс-листы
     */
    public function all(): JsonResponse
    {
        $sharpeningItems = PriceItem::active()
            ->byServiceType(PriceItem::TYPE_SHARPENING)
            ->ordered()
            ->get();

        $repairItems = PriceItem::active()
            ->byServiceType(PriceItem::TYPE_REPAIR)
            ->ordered()
            ->get();

        return response()->json([
            'sharpeningBlocks' => $this->groupByCategory($sharpeningItems),
            'repairBlocks' => $this->groupByCategory($repairItems),
        ]);
    }

    /**
     * Сгруппировать позиции прайса по категориям
     * Элементы уже отсортированы через scopeOrdered, поэтому просто группируем и сохраняем порядок
     */
    private function groupByCategory($items): array
    {
        $blocks = [];
        $categories = [];

        // Проходим по уже отсортированным элементам и группируем их
        foreach ($items as $item) {
            $categoryTitle = $item->category_title;
            
            if (!isset($categories[$categoryTitle])) {
                $categories[$categoryTitle] = [];
            }
            
            $itemData = [
                'name' => $item->name,
                'price' => $item->price,
            ];
            
            if ($item->description) {
                $itemData['description'] = $item->description;
            }
            
            $categories[$categoryTitle][] = $itemData;
        }

        // Преобразуем в нужный формат, сохраняя порядок появления категорий
        foreach ($categories as $categoryTitle => $categoryItems) {
            $blocks[] = [
                'title' => $categoryTitle,
                'items' => $categoryItems,
            ];
        }

        return $blocks;
    }
}
