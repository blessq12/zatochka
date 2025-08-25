<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::factory()->create();
    }

    /** @test */
    public function it_can_create_sharpening_order()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Иван Иванов',
            'client_phone' => '+79001234567',
            'tool_type' => 'manicure',
            'total_tools_count' => 2,
            'needs_delivery' => false,
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'order' => [
                    'id',
                    'order_number',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_tools_count' => 2,
        ]);

        $this->assertDatabaseHas('clients', [
            'full_name' => 'Иван Иванов',
            'phone' => '+79001234567',
        ]);
    }

    /** @test */
    public function it_can_create_repair_order()
    {
        // Arrange
        $orderData = [
            'service_type' => 'repair',
            'client_name' => 'Петр Петров',
            'client_phone' => '+79001234568',
            'equipment_name' => 'Ножницы',
            'problem_description' => 'Тупится лезвие',
            'urgency' => 'normal',
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'order' => [
                    'id',
                    'order_number',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'service_type' => 'repair',
            'equipment_name' => 'Ножницы',
            'problem_description' => 'Тупится лезвие',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_sharpening()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            // missing tool_type and total_tools_count
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tool_type', 'total_tools_count']);
    }

    /** @test */
    public function it_validates_required_fields_for_repair()
    {
        // Arrange
        $orderData = [
            'service_type' => 'repair',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            // missing equipment_name and problem_description
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipment_name', 'problem_description']);
    }

    /** @test */
    public function it_validates_agreements()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            'tool_type' => 'manicure',
            'total_tools_count' => 1,
            'agreement' => false,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['agreement']);
    }

    /** @test */
    public function it_validates_delivery_address_when_needed()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            'tool_type' => 'manicure',
            'total_tools_count' => 1,
            'needs_delivery' => true,
            // missing delivery_address
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act
        $response = $this->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_address']);
    }

    /** @test */
    public function it_can_get_orders_list()
    {
        // Arrange
        Sanctum::actingAs($this->client);
        Order::factory()->count(5)->create(['client_id' => $this->client->id]);

        // Act
        $response = $this->getJson('/api/orders');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'service_type',
                        'status',
                        'total_amount',
                        'created_at',
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_can_get_single_order()
    {
        // Arrange
        Sanctum::actingAs($this->client);
        $order = Order::factory()->create(['client_id' => $this->client->id]);

        // Act
        $response = $this->getJson("/api/orders/{$order->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'service_type',
                    'status',
                    'total_amount',
                    'created_at',
                ]
            ]);
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            'tool_type' => 'manicure',
            'total_tools_count' => 1,
            'agreement' => true,
            'privacy_agreement' => true,
        ];

        // Act - отправляем больше 20 запросов
        for ($i = 0; $i < 25; $i++) {
            $response = $this->postJson('/api/orders', $orderData);

            if ($i >= 20) {
                // После 20 запросов должен быть rate limit
                $response->assertStatus(429);
                break;
            }
        }
    }
}
