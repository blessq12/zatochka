<?php


final readonly class RequestDeliveryCommand
{
    public function __construct(
        public int $deliveryRequestId,
        public string $orderId,
        public string $city,
        public string $street,
        public string $building,
        public ?string $apartment = null,
        public ?string $comment = null,
        public bool $pickup = false,
    ) {}
}
