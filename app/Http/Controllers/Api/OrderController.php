<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
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
            'client_name' => 'required|string|min:2|max:255',
            'client_phone' => 'required|string|min:10|max:20',
            'agreement' => 'required|boolean',
            'privacy_agreement' => 'required|boolean',

            // Поля для заточки
            'tool_type' => 'required_if:service_type,sharpening|string|max:255',
            'total_tools_count' => 'required_if:service_type,sharpening|integer|min:1',
            'needs_delivery' => 'sometimes|boolean',
            'delivery_address' => 'required_if:needs_delivery,true|string|min:10|max:500',

            // Поля для ремонта
            'equipment_name' => 'required_if:service_type,repair|string|max:255',
            'equipment_type' => 'required_if:service_type,repair|string|max:255',
            'problem_description' => 'required_if:service_type,repair|string|min:10|max:1000',
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
            $orderData = [
                'client_id' => $client->id,
                'order_number' => 'Z' . date('Ymd') . '-' . Str::random(6),
                'service_type' => $request->service_type,
                'status' => 'new',
                'total_amount' => $this->calculatePrice($request),
            ];

            // Добавляем специфичные поля в зависимости от типа услуги
            if ($request->service_type === 'sharpening') {
                $orderData['tool_type'] = $request->tool_type;
                $orderData['total_tools_count'] = $request->total_tools_count ?? 1;
                $orderData['problem_description'] = $request->problem_description ?? '';
                $orderData['needs_delivery'] = $request->needs_delivery ?? false;
                if ($request->needs_delivery) {
                    $orderData['delivery_address'] = $request->delivery_address;
                }
            } else if ($request->service_type === 'repair') {
                $orderData['equipment_name'] = $request->equipment_name;
                $orderData['tool_type'] = $request->equipment_type; // Сохраняем тип оборудования в tool_type
                $orderData['problem_description'] = $request->problem_description;
                $orderData['urgency'] = $request->urgency ?? 'normal';
                $orderData['work_description'] = 'Ремонт: ' . $request->equipment_name;
                $orderData['total_tools_count'] = 1; // Для ремонта всегда 1
                $orderData['needs_delivery'] = $request->needs_delivery ?? false;
                if ($request->needs_delivery) {
                    $orderData['delivery_address'] = $request->delivery_address;
                }
            }

            try {
                $order = Order::create($orderData);
            } catch (\Exception $createError) {
                throw $createError;
            }

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
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            switch ($request->equipment_type) {
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
