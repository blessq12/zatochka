<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Eloquent\ClientModel;

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

        $termLower = mb_strtolower($term, 'UTF-8');
        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return ClientModel::query()
            ->with('profileAdditional')
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get()
            ->filter(function (ClientModel $model) use ($termLower, $digits): bool {
                $profile = $model->profileAdditional;
                if ($profile === null) {
                    return false;
                }

                $name = mb_strtolower((string) ($profile->name ?? ''), 'UTF-8');
                if ($termLower !== '' && mb_strpos($name, $termLower) !== false) {
                    return true;
                }

                if ($digits !== '') {
                    $phoneDigits = preg_replace('/\D+/', '', (string) ($profile->phone ?? '')) ?? '';

                    return str_contains($phoneDigits, $digits);
                }

                return false;
            })
            ->take($limit)
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
