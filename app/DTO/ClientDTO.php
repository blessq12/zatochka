<?php

namespace App\DTO;

class ClientDTO extends BaseDTO
{
    public function __construct(
        public int $id,
        public string $full_name,
        public string $phone,
        public ?string $telegram = null,
        public ?string $birth_date = null,
        public ?string $delivery_address = null,
        public ?string $telegram_verified_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
        // Валидация обязательных полей
        if (empty($this->full_name)) {
            throw new \InvalidArgumentException('Full name is required');
        }

        if (empty($this->phone)) {
            throw new \InvalidArgumentException('Phone is required');
        }

        // Валидация формата телефона
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $this->phone)) {
            throw new \InvalidArgumentException('Invalid phone format');
        }

        // Валидация Telegram username
        if ($this->telegram && !preg_match('/^@?[a-zA-Z0-9_]{5,32}$/', $this->telegram)) {
            throw new \InvalidArgumentException('Invalid Telegram username format');
        }

        // Убираем @ из начала Telegram username если есть
        if ($this->telegram && str_starts_with($this->telegram, '@')) {
            $this->telegram = substr($this->telegram, 1);
        }
    }

    /**
     * Создать DTO из запроса регистрации
     */
    public static function fromRegistrationRequest(array $data): static
    {
        // Проверяем обязательные поля
        if (!isset($data['full_name'])) {
            throw new \InvalidArgumentException('Full name is required');
        }
        if (!isset($data['phone'])) {
            throw new \InvalidArgumentException('Phone is required');
        }

        return new static(
            id: 0, // Будет установлено после создания
            full_name: $data['full_name'],
            phone: $data['phone'],
            telegram: $data['telegram'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            delivery_address: $data['delivery_address'] ?? null,
        );
    }

    /**
     * Создать DTO из запроса обновления профиля
     */
    public static function fromUpdateRequest(array $data): static
    {
        return new static(
            id: $data['id'] ?? 0,
            full_name: $data['full_name'] ?? '',
            phone: $data['phone'] ?? '',
            telegram: $data['telegram'] ?? null,
            birth_date: $data['birth_date'] ?? null,
            delivery_address: $data['delivery_address'] ?? null,
        );
    }

    /**
     * Получить данные для создания клиента
     */
    public function getCreateData(): array
    {
        return [
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'telegram' => $this->telegram,
            'birth_date' => $this->birth_date,
            'delivery_address' => $this->delivery_address,
        ];
    }

    /**
     * Получить данные для обновления клиента
     */
    public function getUpdateData(): array
    {
        $data = [];

        if ($this->full_name) {
            $data['full_name'] = $this->full_name;
        }

        if ($this->phone) {
            $data['phone'] = $this->phone;
        }

        if ($this->telegram !== null) {
            $data['telegram'] = $this->telegram;
        }

        if ($this->birth_date !== null) {
            $data['birth_date'] = $this->birth_date;
        }

        if ($this->delivery_address !== null) {
            $data['delivery_address'] = $this->delivery_address;
        }

        return $data;
    }

    /**
     * Проверить, верифицирован ли Telegram
     */
    public function isTelegramVerified(): bool
    {
        return !empty($this->telegram_verified_at);
    }

    /**
     * Получить маску телефона для отображения
     */
    public function getMaskedPhone(): string
    {
        $phone = $this->phone;
        $length = strlen($phone);

        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, 2) . str_repeat('*', $length - 4) . substr($phone, -2);
    }

    /**
     * Получить возраст клиента
     */
    public function getAge(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        $birthDate = new \DateTime($this->birth_date);
        $now = new \DateTime();
        $interval = $now->diff($birthDate);

        return $interval->y;
    }
}
