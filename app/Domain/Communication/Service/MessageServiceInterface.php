<?php

namespace App\Domain\Communication\Service;

interface MessageServiceInterface
{
    public function send(string $recipient, string $message, array $options = []): bool;
}
