<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Получить заказы текущего клиента
     */
    public function orders(Request $request): JsonResponse
    {
        $client = $request->user();

        if (!$client) {
            return response()->json([
                'message' => 'Пользователь не авторизован'
            ], 401);
        }

        $orders = Order::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders,
            'message' => 'Заказы успешно загружены'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $clients = Client::paginate(15);

        return response()->json([
            'data' => $clients->items(),
            'pagination' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ],
            'message' => 'Список клиентов загружен'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone',
            'telegram' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client = Client::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'telegram' => $validated['telegram'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'data' => $client,
            'message' => 'Клиент успешно создан'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        return response()->json([
            'data' => $client,
            'message' => 'Клиент найден'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:clients,phone,' . $id,
            'telegram' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return response()->json([
            'data' => $client,
            'message' => 'Клиент успешно обновлен'
        ]);
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'message' => 'Клиент успешно удален'
        ]);
    }
}
