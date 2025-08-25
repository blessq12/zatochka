<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Facades\Cache;

class FaqService
{
    private const CACHE_TTL = 3600; // 1 час
    private const CACHE_KEY = 'faqs_active';

    /**
     * Получить все активные FAQ
     */
    public function getActiveFaqs()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Faq::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
        });
    }

    /**
     * Получить FAQ по категории
     */
    public function getFaqsByCategory(string $category)
    {
        $cacheKey = "faqs_category_{$category}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category) {
            return Faq::where('is_active', true)
                ->where('category', $category)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
        });
    }

    /**
     * Получить FAQ по ID
     */
    public function getFaqById(int $id): ?Faq
    {
        $cacheKey = "faq_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Faq::find($id);
        });
    }

    /**
     * Создать новый FAQ
     */
    public function createFaq(array $data): Faq
    {
        $faq = Faq::create($data);
        $this->clearCache();
        return $faq;
    }

    /**
     * Обновить FAQ
     */
    public function updateFaq(Faq $faq, array $data): bool
    {
        $result = $faq->update($data);
        $this->clearCache();
        return $result;
    }

    /**
     * Удалить FAQ
     */
    public function deleteFaq(Faq $faq): bool
    {
        $result = $faq->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Получить категории FAQ
     */
    public function getCategories(): array
    {
        $cacheKey = 'faqs_categories';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return Faq::where('is_active', true)
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Поиск FAQ
     */
    public function searchFaqs(string $query)
    {
        $cacheKey = "faqs_search_" . md5($query);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
            return Faq::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('question', 'like', "%{$query}%")
                        ->orWhere('answer', 'like', "%{$query}%");
                })
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
        });
    }

    /**
     * Очистить кеш FAQ
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('faqs_categories');

        // Очищаем кеш поиска
        Cache::flush();
    }

    /**
     * Получить статистику FAQ
     */
    public function getStats(): array
    {
        $cacheKey = 'faqs_stats';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return [
                'total' => Faq::count(),
                'active' => Faq::where('is_active', true)->count(),
                'inactive' => Faq::where('is_active', false)->count(),
                'categories' => Faq::distinct()->count('category'),
            ];
        });
    }
}
