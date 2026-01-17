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
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
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
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
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
            'orderWorks.materials',
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
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
                Order::STATUS_IN_WORK,
                Order::STATUS_WAITING_PARTS,
                Order::STATUS_READY,
                Order::STATUS_ISSUED,
                Order::STATUS_CANCELLED,
            ]),
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Обработка списания/возврата товаров при изменении статуса заказа
        $works = $order->orderWorks()->where('is_deleted', false)->with('materials')->get();

        // Если заказ переводится в статус ready или issued - списываем товары
        if (
            in_array($newStatus, [Order::STATUS_READY, Order::STATUS_ISSUED]) &&
            !in_array($oldStatus, [Order::STATUS_READY, Order::STATUS_ISSUED])
        ) {
            // Списываем товары со склада (используем данные из order_work_materials)
            foreach ($works as $work) {
                foreach ($work->materials as $material) {
                    if ($material->warehouseItem) {
                        $quantity = $material->quantity;
                        // Списание автоматически уменьшает и reserved_quantity, и quantity
                        $material->warehouseItem->decreaseQuantity($quantity);
                    }
                }
            }
        }
        // Если заказ переводится обратно из ready/issued в другой статус - возвращаем товары
        elseif (
            in_array($oldStatus, [Order::STATUS_READY, Order::STATUS_ISSUED]) &&
            !in_array($newStatus, [Order::STATUS_READY, Order::STATUS_ISSUED])
        ) {
            // Возвращаем товары на склад (увеличиваем quantity)
            foreach ($works as $work) {
                foreach ($work->materials as $material) {
                    if ($material->warehouseItem) {
                        $quantity = $material->quantity;
                        // Возвращаем товар на склад и резервируем его снова
                        $material->warehouseItem->increaseQuantity($quantity);
                        $material->warehouseItem->reserve($quantity);
                    }
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
            'work_price' => 'required|numeric|min:0',
        ]);

        $work = \App\Models\OrderWork::create([
            'order_id' => $order->id,
            'work_type' => 'repair', // По умолчанию
            'description' => $request->description,
            'quantity' => 1,
            'work_price' => $request->work_price,
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

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        $request->validate([
            'work_type' => 'sometimes|string|in:repair,sharpening,diagnostic',
            'description' => 'sometimes|string|max:1000',
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'work_price' => 'sometimes|numeric|min:0',
            'work_time_minutes' => 'sometimes|integer|min:0',
        ]);

        $work->update($request->only([
            'work_type',
            'description',
            'quantity',
            'unit_price',
            'work_price',
            'work_time_minutes',
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

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        // Получаем материалы работы перед удалением для снятия резерва
        $workMaterials = $work->materials;

        // Снимаем резерв со всех материалов, если заказ еще не завершен
        if (!in_array($order->status, [Order::STATUS_READY, Order::STATUS_ISSUED])) {
            foreach ($workMaterials as $material) {
                if ($material->warehouseItem) {
                    $quantityToRelease = $material->quantity;
                    $material->warehouseItem->releaseReserve($quantityToRelease);
                }
            }
        }

        $work->update(['is_deleted' => 1]);

        return response()->json(['message' => 'Work deleted successfully']);
    }

    /**
     * Получить материалы заказа
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

        $works = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('is_deleted', 0)
            ->with(['materials'])
            ->get();

        $materials = [];
        foreach ($works as $work) {
            foreach ($work->materials as $material) {
                $materials[] = [
                    'id' => $material->id,
                    'work_id' => $work->id,
                    'warehouse_item_id' => $material->warehouse_item_id,
                    'name' => $material->name,
                    'article' => $material->article,
                    'quantity' => $material->quantity,
                    'price' => $material->price,
                    'notes' => $material->notes,
                ];
            }
        }

        return response()->json(['materials' => $materials]);
    }

    /**
     * Добавить материал к работе заказа
     */
    public function addOrderMaterial(Request $request, $orderId, $workId)
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

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        $request->validate([
            'warehouse_item_id' => 'required|exists:warehouse_items,id',
            'quantity' => 'required|numeric|min:0.001',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $warehouseItem = \App\Models\WarehouseItem::find($request->warehouse_item_id);

        if (!$warehouseItem || !$warehouseItem->is_active) {
            return response()->json(['message' => 'Warehouse item not found or inactive'], 404);
        }

        // Проверяем наличие на складе
        $neededQuantity = $request->quantity;

        // Проверяем, был ли материал уже добавлен к этой работе
        $existingMaterial = $work->materials()
            ->where('warehouse_item_id', $request->warehouse_item_id)
            ->first();
        if ($existingMaterial) {
            // Если материал уже добавлен, нужно снять старый резерв и зарезервировать новое количество
            $oldQuantity = $existingMaterial->quantity;
            // Снимаем старый резерв
            $warehouseItem->releaseReserve($oldQuantity);
            $neededQuantity = $request->quantity; // Новое количество
        }

        if ($warehouseItem->available_quantity < $neededQuantity) {
            return response()->json([
                'message' => 'Not enough stock. Available: ' . $warehouseItem->available_quantity,
            ], 400);
        }

        // Резервируем товар на складе
        if (!$warehouseItem->reserve($neededQuantity)) {
            return response()->json([
                'message' => 'Failed to reserve item. Available: ' . $warehouseItem->available_quantity,
            ], 400);
        }

        // Используем цену из запроса или из товара
        $price = $request->price ?? $warehouseItem->price;

        // Проверяем, был ли материал уже добавлен к этой работе
        $existingMaterial = $work->materials()
            ->where('warehouse_item_id', $request->warehouse_item_id)
            ->first();

        if ($existingMaterial) {
            // Обновляем существующий материал
            $existingMaterial->update([
                'quantity' => $request->quantity,
                'price' => $price,
                'notes' => $request->notes,
                'name' => $warehouseItem->name,
                'article' => $warehouseItem->article,
                'category_name' => $warehouseItem->category?->name,
                'unit' => $warehouseItem->unit ?? 'шт',
            ]);
        } else {
            // Создаем новую запись с snapshot данных
            \App\Models\OrderWorkMaterial::create([
                'work_id' => $work->id,
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

        // Обновляем стоимость материалов в работе
        $totalMaterialsCost = $work->materials()->sum(
            \Illuminate\Support\Facades\DB::raw('quantity * price')
        );
        $work->update(['materials_cost' => $totalMaterialsCost]);

        // Обновляем данные товара
        $warehouseItem->refresh();

        // Получаем созданный/обновленный материал
        $material = $work->materials()
            ->where('warehouse_item_id', $warehouseItem->id)
            ->first();

        return response()->json([
            'message' => 'Material added successfully',
            'material' => [
                'id' => $material->id,
                'warehouse_item_id' => $warehouseItem->id,
                'name' => $material->name,
                'quantity' => $material->quantity,
                'price' => $material->price,
            ],
        ]);
    }

    /**
     * Удалить материал из работы заказа
     */
    public function removeOrderMaterial(Request $request, $orderId, $workId, $materialId)
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

        $work = \App\Models\OrderWork::where('order_id', $order->id)
            ->where('id', $workId)
            ->where('is_deleted', 0)
            ->first();

        if (!$work) {
            return response()->json(['message' => 'Work not found'], 404);
        }

        // Находим материал перед удалением для снятия резерва
        // materialId может быть как ID из order_work_materials, так и warehouse_item_id для обратной совместимости
        $material = $work->materials()
            ->where(function ($query) use ($materialId) {
                $query->where('id', $materialId)
                    ->orWhere('warehouse_item_id', $materialId);
            })
            ->first();

        if ($material) {
            $quantityToRelease = $material->quantity;
            $warehouseItem = $material->warehouseItem;

            // Снимаем резерв только если заказ еще не завершен
            // Если заказ в статусе ready/issued, товар уже списан и резерв не нужно снимать
            if ($warehouseItem && !in_array($order->status, [Order::STATUS_READY, Order::STATUS_ISSUED])) {
                $warehouseItem->releaseReserve($quantityToRelease);
            }

            // Удаляем запись из order_work_materials
            $material->delete();
        }

        // Обновляем стоимость материалов в работе
        $totalMaterialsCost = $work->materials()->sum(
            \Illuminate\Support\Facades\DB::raw('quantity * price')
        );
        $work->update(['materials_cost' => $totalMaterialsCost]);

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
