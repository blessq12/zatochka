<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Создать новый отзыв
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:feedback,testimonial',
            'user_id' => 'nullable|integer|exists:users,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'entity_id' => 'nullable|integer',
            'entity_type' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
            'source' => 'required|string|in:website,telegram,api,external',
            'reply' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        // Автоматически определяем источник, если не указан
        if (!isset($validated['source'])) {
            $validated['source'] = 'api';
        }

        // Добавляем метаданные о запросе
        $metadata = $validated['metadata'] ?? [];
        $metadata['ip_address'] = $request->ip();
        $metadata['user_agent'] = $request->userAgent();
        $metadata['created_via'] = 'api';
        $validated['metadata'] = $metadata;

        // Создаем отзыв
        $review = Review::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Отзыв успешно создан и отправлен на модерацию',
            'data' => [
                'id' => $review->id,
                'status' => $review->status,
                'created_at' => $review->created_at
            ]
        ], 201);
    }

    /**
     * Получить отзывы (только одобренные)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::approved();

        // Фильтрация по типу
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Фильтрация по источнику
        if ($request->has('source')) {
            $query->ofSource($request->source);
        }

        // Фильтрация по заказу
        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        // Фильтрация по пользователю
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Пагинация
        $perPage = $request->get('per_page', 10);
        $reviews = $query->with(['user', 'order'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Получить конкретный отзыв (только одобренный)
     */
    public function show(Review $review): JsonResponse
    {
        if (!$review->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Отзыв не найден'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'order'])
        ]);
    }

    /**
     * Получить статистику отзывов
     */
    public function stats(Request $request): JsonResponse
    {
        $query = Review::approved();

        if ($request->has('type')) {
            $query->ofType($request->type);
        }


        if ($request->has('entity_type') && $request->has('entity_id')) {
            $query->where('entity_type', $request->entity_type)
                ->where('entity_id', $request->entity_id);
        }

        // Фильтрация по источнику
        if ($request->has('source')) {
            $query->ofSource($request->source);
        }

        // Получаем статистику
        $total = $query->count();
        $withRating = $query->withRating()->count();
        $averageRating = $withRating > 0 ? $query->withRating()->avg('rating') : 0;

        // Процент положительных отзывов (4-5 звезд)
        $positiveCount = $query->withRating()->where('rating', '>=', 4)->count();
        $positivePercentage = $withRating > 0 ? round(($positiveCount / $withRating) * 100) : 0;

        // Количество отзывов за текущий месяц
        $recentCount = $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'withRating' => $withRating,
                'averageRating' => round($averageRating, 1),
                'positivePercentage' => $positivePercentage,
                'recentCount' => $recentCount,
            ]
        ]);
    }
}
