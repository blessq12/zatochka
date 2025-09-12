<?php

namespace App\Domain\Order\Mapper;

use App\Domain\Order\Entity\Order;

interface OrderMapper
{
    /**
     * Преобразовать Eloquent модель в доменную сущность
     */
    public function toDomain($eloquentModel): Order;

    /**
     * Преобразовать доменную сущность в массив для Eloquent
     */
    public function toEloquent(Order $domainEntity): array;

    /**
     * Преобразовать массив данных в доменную сущность
     */
    public function fromArray(array $data): Order;
}
