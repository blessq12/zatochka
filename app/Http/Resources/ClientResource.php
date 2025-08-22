<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'telegram' => $this->telegram,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'delivery_address' => $this->delivery_address,
            'telegram_verified_at' => $this->telegram_verified_at?->toISOString(),
            'is_telegram_verified' => $this->isTelegramVerified(),
            'orders_count' => $this->whenLoaded('orders', function () {
                return $this->orders->count();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
