<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;
use App\Domain\Company\Entity\User;
use App\Domain\Company\Enum\UserRole;
use Illuminate\Support\Str;

class CreateUserUseCase extends BaseUserUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Имя пользователя обязательно');
        }

        if (empty($this->data['email'])) {
            throw new \InvalidArgumentException('Email обязателен');
        }

        if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }

        if (empty($this->data['password'])) {
            throw new \InvalidArgumentException('Пароль обязателен');
        }

        if (strlen($this->data['password']) < 8) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 8 символов');
        }

        // Валидация ролей
        if (isset($this->data['role']) && !is_array($this->data['role'])) {
            throw new \InvalidArgumentException('Роли должны быть массивом');
        }

        if (isset($this->data['role']) && !UserRole::validate($this->data['role'])) {
            throw new \InvalidArgumentException('Недопустимые роли');
        }

        return $this;
    }

    public function execute(): User
    {
        // Хешируем пароль
        if (isset($this->data['password'])) {
            $this->data['password'] = bcrypt($this->data['password']);
        }

        // Устанавливаем значения по умолчанию
        $this->data['is_deleted'] = $this->data['is_deleted'] ?? false;

        // Устанавливаем роли по умолчанию если не указаны
        if (!isset($this->data['role']) || empty($this->data['role'])) {
            $this->data['role'] = [UserRole::MANAGER->value];
        }

        // Создаем пользователя через репозиторий
        $user = $this->userRepository->create($this->data);

        return $user;
    }
}
