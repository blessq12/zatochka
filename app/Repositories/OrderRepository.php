<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Client;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Find order by number
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->where('order_number', $orderNumber)
            ->with(['client', 'orderStatus', 'serviceType', 'reviews', 'notifications', 'orderTools', 'repairs'])
            ->first();
    }

    /**
     * Get client orders
     */
    public function getClientOrders(Client $client, int $perPage = 10): LengthAwarePaginator
    {
        return $client->orders()
            ->with(['orderStatus', 'serviceType', 'reviews', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('status', $status)
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by service type
     */
    public function getByServiceType(string $serviceType, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('service_type', $serviceType)
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders with delivery
     */
    public function getWithDelivery(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('needs_delivery', true)
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders statistics
     */
    public function getStats(): array
    {
        $stats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new_orders,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_orders,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                SUM(CASE WHEN needs_delivery = 1 THEN 1 ELSE 0 END) as orders_with_delivery
            ')
            ->first();

        return [
            'total_orders' => $stats->total_orders ?? 0,
            'new_orders' => $stats->new_orders ?? 0,
            'in_progress_orders' => $stats->in_progress_orders ?? 0,
            'completed_orders' => $stats->completed_orders ?? 0,
            'cancelled_orders' => $stats->cancelled_orders ?? 0,
            'total_revenue' => $stats->total_revenue ?? 0,
            'average_order_value' => $stats->average_order_value ?? 0,
            'orders_with_delivery' => $stats->orders_with_delivery ?? 0,
        ];
    }

    /**
     * Search orders
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                    ->orWhereHas('client', function ($clientQuery) use ($query) {
                        $clientQuery->where('full_name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders for today
     */
    public function getTodayOrders(): Collection
    {
        return $this->model
            ->whereDate('created_at', today())
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders for this week
     */
    public function getThisWeekOrders(): Collection
    {
        return $this->model
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders for this month
     */
    public function getThisMonthOrders(): Collection
    {
        return $this->model
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with(['client', 'orderStatus', 'serviceType'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
