<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;
use App\Domain\Company\Entity\User;
use App\Domain\Company\Enum\UserRole;

class UpdateUserUseCase extends BaseUserUseCase
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

        // Валидация email если указан
        if (isset($this->data['email']) && !filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }

        // Валидация пароля если указан
        if (isset($this->data['password']) && strlen($this->data['password']) < 8) {
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
        // Получаем текущего пользователя
        $currentUser = $this->userRepository->get((int) $this->data['id']);
        if (!$currentUser) {
            throw new \InvalidArgumentException('Пользователь не найден');
        }

        // Подготавливаем данные для обновления
        $updateData = $this->data;
        unset($updateData['id']);

        // Хешируем пароль если указан
        if (isset($updateData['password'])) {
            $updateData['password'] = bcrypt($updateData['password']);
        }

        // Если роли не указаны, сохраняем текущие
        if (!isset($updateData['role']) || empty($updateData['role'])) {
            $eloquentUser = \App\Models\User::find($currentUser->getId());
            if ($eloquentUser) {
                $updateData['role'] = $eloquentUser->getRoles();
            }
        }

        // Обновляем пользователя через репозиторий
        $updatedUser = $this->userRepository->update($currentUser, $updateData);

        return $updatedUser;
    }
}
