<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use App\Models\TelegramChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    /**
     * Авторизация мастера через токены
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $master = Master::where('email', $request->email)
            ->where('is_deleted', false)
            ->first();

        if (!$master || !Hash::check($request->password, $master->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Создаем токен через Sanctum с уникальным именем для мастера
        $token = $master->createToken('pos_master_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Выход мастера (удаление токена)
     */
    public function logout(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if ($master) {
            // Удаляем текущий токен
            $master->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Получить информацию о текущем мастере по токену
     */
    public function me(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Обновить профиль мастера
     */
    public function updateProfile(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Убираем @ из telegram_username если есть
        if (isset($data['telegram_username'])) {
            $data['telegram_username'] = ltrim($data['telegram_username'], '@');
            if (empty($data['telegram_username'])) {
                $data['telegram_username'] = null;
            }
        }

        $master->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Получить список заказов для мастера
     */
    public function orders(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $status = $request->get('status'); // new, active, waiting_parts, completed

        $query = Order::with(['client', 'branch', 'master'])
            ->where('is_deleted', false)
            ->where('master_id', $master->id);

        // Фильтр по статусу
        if ($status === 'new') {
            $query->whereIn('status', [
                Order::STATUS_NEW,
            ]);
        } elseif ($status === 'active') {
            $query->where('status', Order::STATUS_IN_WORK);
        } elseif ($status === 'waiting_parts') {
            $query->where('status', Order::STATUS_WAITING_PARTS);
        } elseif ($status === 'completed') {
            $query->whereIn('status', [
                Order::STATUS_READY,
                Order::STATUS_CANCELLED,
            ]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Получить счетчики заказов для мастера
     */
    public function ordersCount(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // Новые заказы (new, consultation, diagnostic)
        $newCount = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->whereIn('status', [
                Order::STATUS_NEW,
            ])
            ->count();

        // Заказы в работе (in_work)
        $inWorkCount = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->where('status', Order::STATUS_IN_WORK)
            ->count();

        // Ожидание запчастей (waiting_parts)
        $waitingPartsCount = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->where('status', Order::STATUS_WAITING_PARTS)
            ->count();

        // Готовые заказы (ready)
        $readyCount = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->where('status', Order::STATUS_READY)
            ->count();

        return response()->json([
            'new' => $newCount,
            'in_work' => $inWorkCount,
            'waiting_parts' => $waitingPartsCount,
            'ready' => $readyCount,
        ]);
    }

    /**
     * Получить статистику для дашборда мастера
     */
    public function dashboard(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        // Базовый запрос для заказов мастера
        $baseQuery = Order::where('is_deleted', 0)
            ->where('master_id', $master->id);

        // Статистика по статусам
        $statusStats = [
            'new' => (clone $baseQuery)->whereIn('status', [
                Order::STATUS_NEW,
            ])->count(),
            'in_work' => (clone $baseQuery)->where('status', Order::STATUS_IN_WORK)->count(),
            'waiting_parts' => (clone $baseQuery)->where('status', Order::STATUS_WAITING_PARTS)->count(),
            'ready' => (clone $baseQuery)->where('status', Order::STATUS_READY)->count(),
        ];

        // Статистика за сегодня
        $todayStats = [
            'completed' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $todayStart)
                ->count(),
            'total_revenue' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $todayStart)
                ->sum('price') ?? 0,
        ];

        // Статистика за неделю
        $weekStats = [
            'completed' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $weekStart)
                ->count(),
            'total_revenue' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $weekStart)
                ->sum('price') ?? 0,
        ];

        // Статистика за месяц
        $monthStats = [
            'completed' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $monthStart)
                ->count(),
            'total_revenue' => (clone $baseQuery)
                ->where('status', Order::STATUS_READY)
                ->where('updated_at', '>=', $monthStart)
                ->sum('price') ?? 0,
        ];

        // Статистика по работам
        $worksStats = [
            'today' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $todayStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $todayStart);
            })
                ->where('is_deleted', 0)
                ->count(),
            'week' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $weekStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $weekStart);
            })
                ->where('is_deleted', 0)
                ->count(),
            'month' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $monthStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $monthStart);
            })
                ->where('is_deleted', 0)
                ->count(),
        ];

        // Общая выручка по работам
        $worksRevenue = [
            'today' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $todayStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $todayStart);
            })
                ->where('is_deleted', 0)
                ->sum('work_price') ?? 0,
            'week' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $weekStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $weekStart);
            })
                ->where('is_deleted', 0)
                ->sum('work_price') ?? 0,
            'month' => \App\Models\OrderWork::whereHas('order', function ($query) use ($master, $monthStart) {
                $query->where('master_id', $master->id)
                    ->where('is_deleted', 0)
                    ->where('created_at', '>=', $monthStart);
            })
                ->where('is_deleted', 0)
                ->sum('work_price') ?? 0,
        ];

        return response()->json([
            'status_stats' => $statusStats,
            'today' => $todayStats,
            'week' => $weekStats,
            'month' => $monthStats,
            'works' => $worksStats,
            'works_revenue' => $worksRevenue,
        ]);
    }

    /**
     * Получить детали заказа по ID
     */
    public function order(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::with([
            'client',
            'branch',
            'master',
            'manager',
            'equipment',
            'orderWorks',
            'orderMaterials',
            'tools',
        ])
            ->where('is_deleted', false)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Обновить заказ (комментарии и другие поля)
     */
    public function updateOrder(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя редактировать выданный заказ',
            ], 422);
        }

        $request->validate([
            'internal_notes' => 'nullable|string|max:5000',
        ]);

        $order->update($request->only(['internal_notes']));

        return response()->json([
            'message' => 'Order updated',
            'order' => $order->fresh(['client', 'branch', 'master']),
        ]);
    }

    /**
     * Обновить статус заказа
     */
    public function updateOrderStatus(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                Order::STATUS_NEW,
                Order::STATUS_IN_WORK,
                Order::STATUS_WAITING_PARTS,
                Order::STATUS_READY,
                Order::STATUS_ISSUED,
                Order::STATUS_CANCELLED,
            ]),
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Запрещаем менять статус уже выданного заказа
        if ($order->isIssued() && $newStatus !== Order::STATUS_ISSUED) {
            return response()->json([
                'message' => 'Нельзя менять статус выданного заказа',
            ], 422);
        }

        // Обработка списания/возврата товаров при изменении статуса заказа
        $works = $order->orderWorks()->where('is_deleted', false)->get();
        $orderMaterials = $order->orderMaterials;

        // Проверяем наличие работ перед переводом в статус ready или issued
        if (
            in_array($newStatus, [Order::STATUS_READY, Order::STATUS_ISSUED]) &&
            !in_array($oldStatus, [Order::STATUS_READY, Order::STATUS_ISSUED])
        ) {
            // Нельзя завершить заказ без выполненных работ
            if ($works->isEmpty()) {
                return response()->json([
                    'message' => 'Нельзя завершить заказ без выполненных работ. Добавьте хотя бы одну работу.',
                ], 422);
            }
            // Списываем товары со склада (материалы привязаны к заказу)
            foreach ($orderMaterials as $material) {
                if ($material->warehouseItem) {
                    $quantity = $material->quantity;
                    $material->warehouseItem->decreaseQuantity($quantity);
                }
            }
        }
        // Если заказ переводится обратно из ready/issued в другой статус - возвращаем товары
        elseif (
            in_array($oldStatus, [Order::STATUS_READY, Order::STATUS_ISSUED]) &&
            !in_array($newStatus, [Order::STATUS_READY, Order::STATUS_ISSUED])
        ) {
            foreach ($orderMaterials as $material) {
                if ($material->warehouseItem) {
                    $quantity = $material->quantity;
                    $material->warehouseItem->increaseQuantity($quantity);
                    $material->warehouseItem->reserve($quantity);
                }
            }
        }

        $order->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order->fresh(['client', 'branch', 'master']),
        ]);
    }

    /**
     * Получить товары склада
     */
    public function warehouseItems(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);

        $query = \App\Models\WarehouseItem::with('category')
            ->where('is_active', true);

        // Поиск по названию или артикулу
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('article', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'items' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    /**
     * Получить работы заказа
     */
    public function getOrderWorks(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя добавлять работы в выданный заказ',
            ], 422);
        }

        $works = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('is_deleted', 0)
            ->with('materials')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['works' => $works]);
    }

    /**
     * Создать работу для заказа
     */
    public function createOrderWork(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $request->validate([
            'description' => 'required|string|max:1000',
            'equipment_component_name' => 'nullable|string|max:255',
            'equipment_component_serial_number' => 'nullable|string|max:255',
        ]);

        // Проверяем что элемент оборудования существует в equipment, если указан
        if ($request->equipment_component_name || $request->equipment_component_serial_number) {
            $equipment = $order->equipment;
            if (!$equipment) {
                return response()->json([
                    'message' => 'У заказа нет привязанного оборудования',
                ], 422);
            }
            
            if ($equipment->serial_number && is_array($equipment->serial_number)) {
                $components = $equipment->serial_number;
                $componentExists = false;
                $requestName = trim($request->equipment_component_name ?? '');
                $requestSn = trim($request->equipment_component_serial_number ?? '');
                
                foreach ($components as $component) {
                    $name = trim($component['name'] ?? '');
                    $sn = trim($component['serial_number'] ?? '');
                    
                    // Проверяем совпадение по обоим полям, если оба указаны, или по одному если указан только один
                    $nameMatch = !$requestName || $name === $requestName;
                    $snMatch = !$requestSn || $sn === $requestSn;
                    
                    if ($nameMatch && $snMatch) {
                        $componentExists = true;
                        break;
                    }
                }
                
                if (!$componentExists) {
                    return response()->json([
                        'message' => 'Указанный элемент оборудования не найден в данном оборудовании',
                    ], 422);
                }
            }
        }

        $work = \App\Models\OrderWork::create([
            'order_id' => $order->id,
            'description' => $request->description,
            'equipment_component_name' => $request->equipment_component_name,
            'equipment_component_serial_number' => $request->equipment_component_serial_number,
            'quantity' => 1,
            'work_price' => 0, // Цена проставляется в админке
        ]);

        return response()->json([
            'message' => 'Work created successfully',
            'work' => $work->fresh(['materials']),
        ], 201);
    }

    /**
     * Обновить работу заказа
     */
    public function updateOrderWork(Request $request, $orderId, $workId)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя редактировать работы выданного заказа',
            ], 422);
        }

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        $request->validate([
            'description' => 'sometimes|string|max:1000',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $work->update($request->only([
            'description',
            'quantity',
        ]));

        return response()->json([
            'message' => 'Work updated successfully',
            'work' => $work->fresh(['materials']),
        ]);
    }

    /**
     * Удалить работу заказа
     */
    public function deleteOrderWork(Request $request, $orderId, $workId)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя удалять работы выданного заказа',
            ], 422);
        }

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        // Материалы привязаны к заказу — при удалении работы переводим их на уровень заказа
        $work->materials()->update(['work_id' => null]);

        $work->update(['is_deleted' => 1]);

        return response()->json(['message' => 'Work deleted successfully']);
    }

    /**
     * Получить материалы заказа (материалы привязаны к заказу)
     */
    public function getOrderMaterials(Request $request, $id)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $materials = $order->orderMaterials->map(fn($material) => [
            'id' => $material->id,
            'warehouse_item_id' => $material->warehouse_item_id,
            'name' => $material->name,
            'article' => $material->article,
            'quantity' => $material->quantity,
            'price' => $material->price,
            'notes' => $material->notes,
        ])->values()->all();

        return response()->json(['materials' => $materials]);
    }

    /**
     * Добавить материал к заказу (материалы привязаны к заказу, не к работам)
     */
    public function addOrderMaterial(Request $request, $orderId)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя добавлять материалы в выданный заказ',
            ], 422);
        }

        $request->validate([
            'warehouse_item_id' => 'required|exists:warehouse_items,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500',
        ]);

        $warehouseItem = \App\Models\WarehouseItem::find($request->warehouse_item_id);

        if (!$warehouseItem || !$warehouseItem->is_active) {
            return response()->json(['message' => 'Warehouse item not found or inactive'], 404);
        }

        $neededQuantity = $request->quantity;

        $existingMaterial = $order->orderMaterials()
            ->where('warehouse_item_id', $request->warehouse_item_id)
            ->first();

        if ($existingMaterial) {
            $warehouseItem->releaseReserve($existingMaterial->quantity);
        }

        if ($warehouseItem->available_quantity < $neededQuantity) {
            return response()->json([
                'message' => 'Not enough stock. Available: ' . $warehouseItem->available_quantity,
            ], 400);
        }

        if (!$warehouseItem->reserve($neededQuantity)) {
            return response()->json([
                'message' => 'Failed to reserve item. Available: ' . $warehouseItem->available_quantity,
            ], 400);
        }

        $price = $warehouseItem->price ?? 0;

        if ($existingMaterial) {
            $existingMaterial->update([
                'quantity' => $request->quantity,
                'price' => $price,
                'notes' => $request->notes,
                'name' => $warehouseItem->name,
                'article' => $warehouseItem->article,
                'category_name' => $warehouseItem->category?->name,
                'unit' => $warehouseItem->unit ?? 'шт',
            ]);
            $material = $existingMaterial;
        } else {
            $material = \App\Models\OrderWorkMaterial::create([
                'work_id' => null,
                'order_id' => $order->id,
                'warehouse_item_id' => $warehouseItem->id,
                'name' => $warehouseItem->name,
                'article' => $warehouseItem->article,
                'category_name' => $warehouseItem->category?->name,
                'unit' => $warehouseItem->unit ?? 'шт',
                'price' => $price,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
            ]);
        }

        $warehouseItem->refresh();

        return response()->json([
            'message' => 'Material added successfully',
            'material' => [
                'id' => $material->id,
                'warehouse_item_id' => $warehouseItem->id,
                'name' => $material->name,
                'quantity' => $material->quantity,
                'price' => $material->price,
            ],
        ], 201);
    }

    /**
     * Удалить материал из заказа
     */
    public function removeOrderMaterial(Request $request, $orderId, $materialId)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('is_deleted', 0)
            ->where('master_id', $master->id)
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->isIssued()) {
            return response()->json([
                'message' => 'Нельзя удалять материалы выданного заказа',
            ], 422);
        }

        $material = $order->orderMaterials()
            ->where(function ($query) use ($materialId) {
                $query->where('id', $materialId)
                    ->orWhere('warehouse_item_id', $materialId);
            })
            ->first();

        if ($material) {
            $quantityToRelease = $material->quantity;
            $warehouseItem = $material->warehouseItem;

            if ($warehouseItem && !in_array($order->status, [Order::STATUS_READY, Order::STATUS_ISSUED])) {
                $warehouseItem->releaseReserve($quantityToRelease);
            }

            $material->delete();
        }

        return response()->json(['message' => 'Material removed successfully']);
    }

    /**
     * Отправить код верификации Telegram для мастера
     */
    public function sendTelegramVerificationCode(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$master->telegram_username) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram username not specified in profile',
            ], 400);
        }

        // Проверяем, не подтвержден ли уже Telegram
        if ($master->telegram_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram already verified',
            ], 400);
        }

        // Генерируем 6-значный код
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Сохраняем код в кеш на 5 минут (ключ: master_id + username)
        $cacheKey = "telegram_verification_master:{$master->id}:{$master->telegram_username}";
        Cache::put($cacheKey, [
            'code' => $code,
            'master_id' => $master->id,
            'username' => $master->telegram_username,
        ], now()->addMinutes(5));

        // Находим чат по username
        $telegramChat = TelegramChat::byUsername($master->telegram_username)->active()->first();

        if (!$telegramChat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found. Please send /start to the bot first',
            ], 404);
        }

        // Отправляем код в Telegram
        $botToken = config('services.telegram.bot_token');
        $message = "🔐 Код верификации: <b>{$code}</b>\n\nВведите этот код в панели мастера или отправьте мне для подтверждения.";
        $this->sendTelegramMessage($botToken, $telegramChat->chat_id, $message);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent',
            'telegram_username' => $master->telegram_username,
            'expires_in_minutes' => 5,
        ]);
    }

    /**
     * Проверка кода верификации Telegram для мастера
     */
    public function verifyTelegramCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$master->telegram_username) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram username not specified',
            ], 400);
        }

        $code = $request->input('code');

        // Проверяем код в кеше
        $cacheKey = "telegram_verification_master:{$master->id}:{$master->telegram_username}";
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['code'] !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code',
            ], 400);
        }

        // Находим чат
        $telegramChat = TelegramChat::byUsername($master->telegram_username)->active()->first();

        if (!$telegramChat) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram chat not found',
            ], 404);
        }

        // Обновляем мастера
        $master->update([
            'telegram_verified_at' => now(),
        ]);

        // Удаляем код из кеша
        Cache::forget($cacheKey);

        // Отправляем подтверждение в Telegram
        $botToken = config('services.telegram.bot_token');
        $message = "✅ Telegram успешно подтвержден!\n\nТеперь вы будете получать уведомления о заказах автоматически.";
        $this->sendTelegramMessage($botToken, $telegramChat->chat_id, $message);

        return response()->json([
            'success' => true,
            'message' => 'Telegram verified successfully',
            'telegram_username' => $master->telegram_username,
            'verified_at' => $master->telegram_verified_at->toIso8601String(),
            'user' => $master->fresh(),
        ]);
    }

    /**
     * Отправить сообщение в Telegram
     */
    private function sendTelegramMessage(string $botToken, int $chatId, string $message, bool $withKeyboard = false): void
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        if ($withKeyboard) {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '👤 Аккаунт', 'callback_data' => 'account'],
                        ['text' => '📋 Активные заказы', 'callback_data' => 'active_orders'],
                    ],
                    [
                        ['text' => '📚 История заказов', 'callback_data' => 'history_orders'],
                    ],
                ],
            ];
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Telegram send message error: ' . $e->getMessage());
        }
    }
}
