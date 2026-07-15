<?php

namespace App\Application\CRM\Presenter;

use App\Application\CRM\DTO\ClientDTO;

interface ClientPresenter
{
    /** @return array<string, mixed> */
    public function present(ClientDTO $client): array;
}
