<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//use cases
use App\Application\UseCases\Review\CreateReviewUseCase;

class TestController extends Controller
{
    public function create(Request $request)
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'client_id' => 'required|integer|exists:clients,id',
                'order_id' => 'required|integer|exists:orders,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'metadata' => 'sometimes|array',
            ]);

            // Создание отзыва через Use Case
            $review = app(CreateReviewUseCase::class)
                ->loadData($validated)
                ->validate()
                ->execute();

            return response()->json([
                'message' => 'Отзыв успешно создан',
                'review_id' => $review->getId(),
                'data' => [
                    'id' => $review->getId(),
                    'client_id' => $review->getClientId(),
                    'order_id' => $review->getOrderId(),
                    'rating' => $review->getRating(),
                    'comment' => $review->getComment(),
                    'is_approved' => $review->isApproved(),
                    'created_at' => $review->getCreatedAt(),
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Ошибка данных',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Внутренняя ошибка сервера',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
