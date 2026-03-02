<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:65535',
        ]);

        /** @var \App\Models\Client $client */
        $client = auth('sanctum')->user();

        $order = $client->orders()->find($request->order_id);
        if (! $order) {
            return response()->json(['message' => 'Заказ не найден или не принадлежит вам'], 403);
        }

        if ($order->review()->exists()) {
            return response()->json(['message' => 'По этому заказу уже оставлен отзыв'], 422);
        }

        $review = Review::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false,
            'is_visible' => false,
        ]);

        return response()->json([
            'message' => 'Отзыв создан и отправлен на модерацию',
            'review' => $review->load('order:id,order_number'),
        ], 201);
    }

    public function getByOrder(int $orderId)
    {
        /** @var \App\Models\Client $client */
        $client = auth('sanctum')->user();

        $order = $client->orders()->find($orderId);
        if (! $order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        $review = $order->review;
        if (! $review) {
            return response()->json(['review' => null], 200);
        }

        return response()->json([
            'review' => $review->load('order:id,order_number'),
        ]);
    }
}
