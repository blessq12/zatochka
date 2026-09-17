<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Eloquent\ClientModel;
use Illuminate\Database\Eloquent\Builder;

final class EloquentClientRepository extends EloquentActorRepository implements ClientRepository
{
    public function __construct()
    {
        parent::__construct(
            ClientModel::class,
            static fn (ClientModel $model): Client => new Client(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ),
        );
    }

    public function searchByNameOrPhone(string $query, int $limit = 20): array
    {
        $term = trim($query);
        if ($term === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';

        $builder = ClientModel::query()
            ->whereHas('profileAdditional', function (Builder $q) use ($term, $digits): void {
                $q->where('name', 'like', '%'.$term.'%');
                if ($digits !== '') {
                    $q->orWhere('phone', 'like', '%'.$digits.'%');
                }
            })
            ->orderByDesc('updated_at')
            ->limit($limit);

        return $builder
            ->get()
            ->map(fn (ClientModel $model): Client => new Client(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ))
            ->values()
            ->all();
    }

    public function recent(int $limit = 8): array
    {
        return ClientModel::query()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ClientModel $model): Client => new Client(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ))
            ->values()
            ->all();
    }
}
