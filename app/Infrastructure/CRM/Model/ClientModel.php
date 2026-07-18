<?php

namespace App\Infrastructure\CRM\Model;

use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Infrastructure\Order\Model\OrderModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class ClientModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'clients';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'phone',
        'name',
        'email',
        'birth_date',
        'delivery_address',
        'password',
        'bonus_account_id',
        'bonus_balance',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'bonus_balance' => 'string',
            'birth_date' => 'date:Y-m-d',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function history(): HasMany
    {
        return $this->hasMany(ClientHistoryModel::class, 'client_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'client_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(ClientEquipmentModel::class, 'client_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ReviewModel::class, 'client_id');
    }
}
