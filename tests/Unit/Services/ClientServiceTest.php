<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientService = new ClientService();
    }

    /** @test */
    public function it_can_create_client()
    {
        // Arrange
        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'telegram' => '@ivan',
            'password' => 'password123',
        ];

        // Act
        $client = $this->clientService->createClient($clientData);

        // Assert
        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('Иван Иванов', $client->full_name);
        $this->assertEquals('+79001234567', $client->phone);
        $this->assertEquals('@ivan', $client->telegram);
        $this->assertTrue(Hash::check('password123', $client->password));
    }

    /** @test */
    public function it_can_find_client_by_phone()
    {
        // Arrange
        $client = Client::factory()->create(['phone' => '+79001234567']);

        // Act
        $foundClient = $this->clientService->findByPhone('+79001234567');

        // Assert
        $this->assertInstanceOf(Client::class, $foundClient);
        $this->assertEquals($client->id, $foundClient->id);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_phone()
    {
        // Act
        $client = $this->clientService->findByPhone('+79001234568');

        // Assert
        $this->assertNull($client);
    }

    /** @test */
    public function it_can_authenticate_client()
    {
        // Arrange
        $client = Client::factory()->create([
            'phone' => '+79001234567',
            'password' => Hash::make('password123')
        ]);

        // Act
        $authenticatedClient = $this->clientService->authenticate('+79001234567', 'password123');

        // Assert
        $this->assertInstanceOf(Client::class, $authenticatedClient);
        $this->assertEquals($client->id, $authenticatedClient->id);
    }

    /** @test */
    public function it_returns_null_for_wrong_credentials()
    {
        // Arrange
        Client::factory()->create([
            'phone' => '+79001234567',
            'password' => Hash::make('password123')
        ]);

        // Act
        $client = $this->clientService->authenticate('+79001234567', 'wrongpassword');

        // Assert
        $this->assertNull($client);
    }

    /** @test */
    public function it_can_update_client_profile()
    {
        // Arrange
        $client = Client::factory()->create();
        $updateData = [
            'full_name' => 'Новое Имя',
            'telegram' => '@newtelegram',
        ];

        // Act
        $result = $this->clientService->updateProfile($client, $updateData);

        // Assert
        $this->assertTrue($result);
        $client->refresh();
        $this->assertEquals('Новое Имя', $client->full_name);
        $this->assertEquals('@newtelegram', $client->telegram);
    }

    /** @test */
    public function it_can_change_password()
    {
        // Arrange
        $client = Client::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        // Act
        $result = $this->clientService->changePassword($client, 'oldpassword', 'newpassword');

        // Assert
        $this->assertTrue($result);
        $client->refresh();
        $this->assertTrue(Hash::check('newpassword', $client->password));
    }

    /** @test */
    public function it_throws_exception_for_wrong_current_password()
    {
        // Arrange
        $client = Client::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->clientService->changePassword($client, 'wrongpassword', 'newpassword');
    }

    /** @test */
    public function it_can_mark_telegram_as_verified()
    {
        // Arrange
        $client = Client::factory()->create(['telegram_verified_at' => null]);

        // Act
        $result = $this->clientService->markTelegramAsVerified($client);

        // Assert
        $this->assertTrue($result);
        $client->refresh();
        $this->assertNotNull($client->telegram_verified_at);
    }

    /** @test */
    public function it_can_create_token()
    {
        // Arrange
        $client = Client::factory()->create();

        // Act
        $token = $this->clientService->createToken($client);

        // Assert
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /** @test */
    public function it_can_get_client_stats()
    {
        // Arrange
        $client = Client::factory()->create();
        // Создаем заказы для клиента
        $client->orders()->createMany([
            ['order_number' => 'Z001', 'total_amount' => 1000, 'status' => 'completed'],
            ['order_number' => 'Z002', 'total_amount' => 1500, 'status' => 'completed'],
            ['order_number' => 'Z003', 'total_amount' => 800, 'status' => 'new'],
        ]);

        // Act
        $stats = $this->clientService->getClientStats($client);

        // Assert
        $this->assertIsArray($stats);
        $this->assertEquals(3, $stats['total_orders']);
        $this->assertEquals(2, $stats['completed_orders']);
        $this->assertEquals(3300, $stats['total_spent']);
    }
}
