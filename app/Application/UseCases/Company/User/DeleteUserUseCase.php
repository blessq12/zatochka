<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;

class DeleteUserUseCase extends BaseUserUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID пользователя обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID пользователя должен быть числом');
        }

        // Проверяем существование пользователя
        if (!$this->userRepository->exists((int) $this->data['id'])) {
            throw new \InvalidArgumentException('Пользователь не найден');
        }

        // Проверяем, что пользователь не пытается удалить сам себя
        if ($this->authContext->isAuthenticated() && $this->authContext->getCurrentUserId() == (int) $this->data['id']) {
            throw new \InvalidArgumentException('Нельзя удалить самого себя');
        }

        return $this;
    }

    public function execute(): bool
    {
        return $this->userRepository->delete((int) $this->data['id']);
    }
}
