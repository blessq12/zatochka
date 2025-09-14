<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Company\Entity\User;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserUseCase extends BaseAuthUseCase
{
    protected function validateSpecificData(): self
    {
        if (empty($this->data['email'])) {
            throw new \InvalidArgumentException('Email обязателен');
        }

        if (empty($this->data['password'])) {
            throw new \InvalidArgumentException('Пароль обязателен');
        }

        if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный формат email');
        }

        return $this;
    }

    public function execute(): ?User
    {
        $email = $this->data['email'];
        $password = $this->data['password'];

        // Ищем пользователя по email
        $eloquentUser = \App\Models\User::where('email', $email)
            ->where('is_deleted', false)
            ->first();

        if (!$eloquentUser) {
            throw new \InvalidArgumentException('Пользователь не найден');
        }

        // Проверяем пароль
        if (!password_verify($password, $eloquentUser->password)) {
            throw new \InvalidArgumentException('Неверный пароль');
        }

        // Проверяем, что у пользователя есть хотя бы одна роль
        if (empty($eloquentUser->getRoles())) {
            throw new \InvalidArgumentException('У пользователя не назначены роли');
        }

        // Возвращаем доменную сущность пользователя
        return $this->userMapper->toDomain($eloquentUser);
    }

    /**
     * Авторизация через Laravel Auth с возвратом доменной сущности
     */
    public function executeWithLaravelAuth(): ?User
    {
        $email = $this->data['email'];
        $password = $this->data['password'];

        // Пытаемся авторизовать через Laravel Auth
        if (!Auth::attempt([
            'email' => $email,
            'password' => $password,
            'is_deleted' => false
        ])) {
            throw new \InvalidArgumentException('Неверные учетные данные');
        }

        $eloquentUser = Auth::user();

        if (empty($eloquentUser->getRoles())) {
            Auth::logout();
            throw new \InvalidArgumentException('У пользователя не назначены роли');
        }

        // Возвращаем доменную сущность
        return $this->userMapper->toDomain($eloquentUser);
    }
}
