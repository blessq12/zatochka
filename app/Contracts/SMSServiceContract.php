<?php

namespace App\Contracts;

interface SMSServiceContract
{
    public function sendSMS(string $phone, string $message): bool;

    public function sendOrderConfirmation(string $phone, string $orderNumber, float $amount): bool;
}
