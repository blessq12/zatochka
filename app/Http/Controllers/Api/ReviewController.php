<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Создает новый отзыв на заказ
     */
    public function createReview(Request $request)
    {
        try {
            // Получаем клиента через токен авторизации
            $client = Auth::guard('sanctum')->user();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходима авторизация'
                ], 401);
            }

            // Валидируем данные
            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
            ]);

            // Проверяем, что заказ принадлежит клиенту
            $order = Order::where('id', $validated['order_id'])
                ->where('client_id', $client->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден или не принадлежит вам'
                ], 404);
            }

            // Проверяем, что заказ завершен (статус "выдан")
            if ($order->status !== Order::STATUS_ISSUED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Отзыв можно оставить только на завершенные заказы'
                ], 400);
            }

            // Проверяем, что отзыв еще не оставлен
            $existingReview = Review::where('client_id', $client->id)
                ->where('order_id', $validated['order_id'])
                ->where('is_deleted', false)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Отзыв на этот заказ уже оставлен'
                ], 400);
            }

            // Создаем отзыв в статусе не проверенного
            $review = Review::create([
                'client_id' => $client->id,
                'order_id' => $validated['order_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'is_approved' => false, // Не проверен
                'is_visible' => false, // Не виден до одобрения
                'is_deleted' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Отзыв успешно создан и отправлен на модерацию',
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'is_approved' => $review->is_approved,
                    'created_at' => $review->created_at->format('d.m.Y H:i'),
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации данных',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании отзыва'
            ], 500);
        }
    }
}
