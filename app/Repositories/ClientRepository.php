<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository extends BaseRepository
{
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    /**
     * Find client by phone
     */
    public function findByPhone(string $phone): ?Client
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Find client by Telegram username
     */
    public function findByTelegram(string $telegram): ?Client
    {
        return $this->model->where('telegram', $telegram)->first();
    }

    /**
     * Get clients with Telegram
     */
    public function getWithTelegram(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereNotNull('telegram')
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get verified Telegram clients
     */
    public function getVerifiedTelegramClients(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereNotNull('telegram_verified_at')
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active clients (with orders)
     */
    public function getActiveClients(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereHas('orders')
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get clients with reviews
     */
    public function getClientsWithReviews(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereHas('reviews')
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search clients
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('telegram', 'like', "%{$query}%");
            })
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent clients
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model
            ->with(['orders', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get clients statistics
     */
    public function getStats(): array
    {
        return [
            'total_clients' => $this->model->count(),
            'clients_with_telegram' => $this->model->whereNotNull('telegram')->count(),
            'verified_telegram_clients' => $this->model->whereNotNull('telegram_verified_at')->count(),
            'active_clients' => $this->model->whereHas('orders')->count(),
            'clients_with_reviews' => $this->model->whereHas('reviews')->count(),
            'new_clients_today' => $this->model->whereDate('created_at', today())->count(),
            'new_clients_this_week' => $this->model->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_clients_this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get clients for birthday today
     */
    public function getBirthdayClients(): Collection
    {
        return $this->model
            ->whereNotNull('birth_date')
            ->whereRaw('DATE_FORMAT(birth_date, "%m-%d") = ?', [now()->format('m-d')])
            ->with(['orders', 'reviews'])
            ->get();
    }
}
