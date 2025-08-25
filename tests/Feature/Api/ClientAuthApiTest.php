<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientAuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_new_client()
    {
        // Arrange
        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/client/register', $clientData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'client' => [
                    'id',
                    'full_name',
                    'phone',
                ],
                'token'
            ]);

        $this->assertDatabaseHas('clients', [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_registration()
    {
        // Arrange
        $clientData = [
            'full_name' => 'Иван Иванов',
            // missing phone and password
        ];

        // Act
        $response = $this->postJson('/api/client/register', $clientData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'password']);
    }

    /** @test */
    public function it_validates_password_confirmation()
    {
        // Arrange
        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        // Act
        $response = $this->postJson('/api/client/register', $clientData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_validates_unique_phone()
    {
        // Arrange
        Client::factory()->create(['phone' => '+79001234567']);

        $clientData = [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/client/register', $clientData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function it_can_login_existing_client()
    {
        // Arrange
        $client = Client::factory()->create([
            'phone' => '+79001234567',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'phone' => '+79001234567',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/client/login', $loginData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'client' => [
                    'id',
                    'full_name',
                    'phone',
                ],
                'token'
            ]);
    }

    /** @test */
    public function it_validates_login_credentials()
    {
        // Arrange
        $client = Client::factory()->create([
            'phone' => '+79001234567',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'phone' => '+79001234567',
            'password' => 'wrongpassword',
        ];

        // Act
        $response = $this->postJson('/api/client/login', $loginData);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Неверные учетные данные'
            ]);
    }

    /** @test */
    public function it_can_logout_client()
    {
        // Arrange
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/client/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Успешный выход из системы'
            ]);
    }

    /** @test */
    public function it_can_get_client_profile()
    {
        // Arrange
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/client/profile');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'full_name',
                    'phone',
                    'telegram',
                    'created_at',
                ]
            ]);
    }

    /** @test */
    public function it_can_update_client_profile()
    {
        // Arrange
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $updateData = [
            'full_name' => 'Новое Имя',
            'telegram' => '@newtelegram',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/client/profile', $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Профиль успешно обновлен'
            ]);

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
        $token = $client->createToken('test-token')->plainTextToken;

        $passwordData = [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/client/change-password', $passwordData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пароль успешно изменен'
            ]);

        $client->refresh();
        $this->assertTrue(Hash::check('newpassword123', $client->password));
    }

    /** @test */
    public function it_validates_current_password()
    {
        // Arrange
        $client = Client::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);
        $token = $client->createToken('test-token')->plainTextToken;

        $passwordData = [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/client/change-password', $passwordData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function it_can_check_token_validity()
    {
        // Arrange
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/client/check-token');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true
            ]);
    }

    /** @test */
    public function it_enforces_rate_limiting_on_auth_endpoints()
    {
        // Arrange
        $loginData = [
            'phone' => '+79001234567',
            'password' => 'password123',
        ];

        // Act - отправляем больше 20 запросов
        for ($i = 0; $i < 25; $i++) {
            $response = $this->postJson('/api/client/login', $loginData);

            if ($i >= 20) {
                // После 20 запросов должен быть rate limit
                $response->assertStatus(429);
                break;
            }
        }
    }
}
