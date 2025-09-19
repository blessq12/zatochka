<?php

namespace App\Domain\Communication\Service;

interface SmsServiceInterface
{
    public function send(string $phone, string $message, array $options = []): bool;
}
