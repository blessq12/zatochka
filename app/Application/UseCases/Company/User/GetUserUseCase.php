<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;
use App\Domain\Company\Entity\User;

class GetUserUseCase extends BaseUserUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID пользователя обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID пользователя должен быть числом');
        }

        return $this;
    }

    public function execute(): ?User
    {
        return $this->userRepository->get((int) $this->data['id']);
    }
}
