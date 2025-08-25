<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'needs_delivery' => $this->needs_delivery,
            'delivery_address' => $this->delivery_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Отношения
            'client' => new ClientResource($this->whenLoaded('client')),
            'order_status' => new OrderStatusResource($this->whenLoaded('orderStatus')),
            'service_type_info' => new ServiceTypeResource($this->whenLoaded('serviceType')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'notifications' => NotificationResource::collection($this->whenLoaded('notifications')),
        ];
    }
}
