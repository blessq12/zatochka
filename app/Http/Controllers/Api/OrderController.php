<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type' => 'required|in:sharpening,repair',
            'tool_type' => 'required|string|max:255',
            'total_tools_count' => 'required_if:service_type,sharpening|integer|min:1',
            'problem_description' => 'required|string|min:10',
            'client_name' => 'required|string|min:2|max:255',
            'client_phone' => 'required|string|min:10|max:20',
            'equipment_name' => 'required_if:service_type,repair|string|max:255',
            'urgency' => 'sometimes|in:normal,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Находим или создаем клиента
            $client = Client::firstOrCreate(
                ['phone' => $request->client_phone],
                [
                    'full_name' => $request->client_name,
                    'phone' => $request->client_phone,
                ]
            );

            // Создаем заказ
            $order = Order::create([
                'client_id' => $client->id,
                'order_number' => 'Z' . date('Ymd') . '-' . Str::random(6),
                'service_type' => $request->service_type,
                'tool_type' => $request->tool_type,
                'problem_description' => $request->problem_description,
                'total_tools_count' => $request->total_tools_count ?? 1,
                'status' => 'new',
                'total_amount' => $this->calculatePrice($request),
                'work_description' => $request->service_type === 'repair' ? 'Ремонт: ' . $request->equipment_name : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно создана',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании заявки',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Рассчитывает примерную стоимость заказа
     */
    private function calculatePrice(Request $request): float
    {
        $basePrice = 0;

        if ($request->service_type === 'sharpening') {
            switch ($request->tool_type) {
                case 'manicure':
                    $basePrice = 500;
                    break;
                case 'hair':
                    $basePrice = 800;
                    break;
                case 'grooming':
                    $basePrice = 700;
                    break;
                default:
                    $basePrice = 600;
            }

            $totalPrice = $basePrice * ($request->total_tools_count ?? 1);
        } else {
            // Ремонт
            switch ($request->tool_type) {
                case 'clipper':
                    $basePrice = 1000;
                    break;
                case 'dryer':
                    $basePrice = 800;
                    break;
                case 'scissors':
                    $basePrice = 1500;
                    break;
                case 'trimmer':
                    $basePrice = 600;
                    break;
                case 'ultrasonic':
                    $basePrice = 2000;
                    break;
                default:
                    $basePrice = 1000;
            }

            // Срочный ремонт +50%
            if ($request->urgency === 'urgent') {
                $basePrice *= 1.5;
            }

            $totalPrice = $basePrice;
        }

        return $totalPrice;
    }
}
