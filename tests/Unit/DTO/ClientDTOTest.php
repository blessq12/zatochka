<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use App\DTO\ClientDTO;
use InvalidArgumentException;

class ClientDTOTest extends TestCase
{
    /** @test */
    public function it_creates_dto_from_registration_request()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'telegram' => '@testuser',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);

        $this->assertEquals('Иван Иванов', $dto->full_name);
        $this->assertEquals('+79001234567', $dto->phone);
        $this->assertEquals('testuser', $dto->telegram); // @ убирается автоматически
    }

    /** @test */
    public function it_creates_dto_from_update_request()
    {
        $data = [
            'id' => 1,
            'full_name' => 'Новое Имя',
            'phone' => '+79001234567',
            'telegram' => 'newuser',
        ];

        $dto = ClientDTO::fromUpdateRequest($data);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('Новое Имя', $dto->full_name);
        $this->assertEquals('+79001234567', $dto->phone);
        $this->assertEquals('newuser', $dto->telegram);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Full name is required');

        $data = [
            'phone' => '+79001234567',
        ];

        ClientDTO::fromRegistrationRequest($data);
    }

    /** @test */
    public function it_validates_phone_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone format');

        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => 'invalid-phone',
        ];

        ClientDTO::fromRegistrationRequest($data);
    }

    /** @test */
    public function it_validates_telegram_username_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Telegram username format');

        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'telegram' => 'invalid@username',
        ];

        ClientDTO::fromRegistrationRequest($data);
    }

    /** @test */
    public function it_removes_at_symbol_from_telegram_username()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'telegram' => '@testuser',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);

        $this->assertEquals('testuser', $dto->telegram);
    }

    /** @test */
    public function it_returns_create_data_array()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'telegram' => '@testuser',
            'birth_date' => '1990-01-01',
            'delivery_address' => 'Москва, ул. Примерная, 1',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);
        $createData = $dto->getCreateData();

        $this->assertIsArray($createData);
        $this->assertEquals('Иван Иванов', $createData['full_name']);
        $this->assertEquals('+79001234567', $createData['phone']);
        $this->assertEquals('testuser', $createData['telegram']);
        $this->assertEquals('1990-01-01', $createData['birth_date']);
        $this->assertEquals('Москва, ул. Примерная, 1', $createData['delivery_address']);
    }

    /** @test */
    public function it_returns_update_data_array()
    {
        $data = [
            'id' => 1,
            'full_name' => 'Новое Имя',
            'phone' => '+79001234567',
            'telegram' => 'newuser',
        ];

        $dto = ClientDTO::fromUpdateRequest($data);
        $updateData = $dto->getUpdateData();

        $this->assertIsArray($updateData);
        $this->assertEquals('Новое Имя', $updateData['full_name']);
        $this->assertEquals('+79001234567', $updateData['phone']);
        $this->assertEquals('newuser', $updateData['telegram']);
    }

    /** @test */
    public function it_checks_telegram_verification()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);
        $dto->telegram_verified_at = '2023-01-01 12:00:00';

        $this->assertTrue($dto->isTelegramVerified());

        $dto->telegram_verified_at = null;
        $this->assertFalse($dto->isTelegramVerified());
    }

    /** @test */
    public function it_masks_phone_number()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);
        $maskedPhone = $dto->getMaskedPhone();

        $this->assertEquals('+7********67', $maskedPhone);
    }

    /** @test */
    public function it_calculates_age()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'birth_date' => '1990-01-01',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);
        $age = $dto->getAge();

        $this->assertIsInt($age);
        $this->assertGreaterThan(0, $age);
    }

    /** @test */
    public function it_returns_null_age_when_birth_date_not_provided()
    {
        $data = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
        ];

        $dto = ClientDTO::fromRegistrationRequest($data);
        $age = $dto->getAge();

        $this->assertNull($age);
    }
}
