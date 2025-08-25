<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
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
            'total_tools_count' => 3,
            'needs_delivery' => false,
        ];

        // Act
        $order = $this->orderService->createOrder($orderData);

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('sharpening', $order->service_type);
        $this->assertEquals('manicure', $order->tool_type);
        $this->assertEquals(3, $order->total_tools_count);
        $this->assertFalse($order->needs_delivery);
        $this->assertStringStartsWith('Z', $order->order_number);
        $this->assertNotNull($order->client);
        $this->assertEquals('Иван Иванов', $order->client->full_name);
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
        ];

        // Act
        $order = $this->orderService->createOrder($orderData);

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('repair', $order->service_type);
        $this->assertEquals('Ножницы', $order->equipment_name);
        $this->assertEquals('Тупится лезвие', $order->problem_description);
        $this->assertEquals(1, $order->total_tools_count);
    }

    /** @test */
    public function it_can_find_order_by_number()
    {
        // Arrange
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'order_number' => 'Z20241201-ABC123'
        ]);

        // Act
        $foundOrder = $this->orderService->findByOrderNumber('Z20241201-ABC123');

        // Assert
        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertEquals($order->id, $foundOrder->id);
        $this->assertEquals('Z20241201-ABC123', $foundOrder->order_number);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_order_number()
    {
        // Act
        $order = $this->orderService->findByOrderNumber('NONEXISTENT');

        // Assert
        $this->assertNull($order);
    }

    /** @test */
    public function it_can_get_client_orders()
    {
        // Arrange
        $client = Client::factory()->create();
        Order::factory()->count(5)->create(['client_id' => $client->id]);

        // Act
        $orders = $this->orderService->getClientOrders($client, 10);

        // Assert
        $this->assertEquals(5, $orders->total());
        $this->assertEquals(10, $orders->perPage());
    }

    /** @test */
    public function it_calculates_price_correctly_for_sharpening()
    {
        // Arrange
        $orderData = [
            'service_type' => 'sharpening',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            'tool_type' => 'manicure',
            'total_tools_count' => 2,
        ];

        // Act
        $order = $this->orderService->createOrder($orderData);

        // Assert
        // manicure = 500 * 2 = 1000
        $this->assertEquals(1000, $order->total_amount);
    }

    /** @test */
    public function it_calculates_price_correctly_for_repair()
    {
        // Arrange
        $orderData = [
            'service_type' => 'repair',
            'client_name' => 'Тест',
            'client_phone' => '+79001234567',
            'equipment_name' => 'Ножницы',
            'problem_description' => 'Простой ремонт',
        ];

        // Act
        $order = $this->orderService->createOrder($orderData);

        // Assert
        // repair base = 1000
        $this->assertEquals(1000, $order->total_amount);
    }
}
