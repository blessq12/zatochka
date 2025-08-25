<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use App\DTO\CreateOrderDTO;
use InvalidArgumentException;

class CreateOrderDTOTest extends TestCase
{
    /** @test */
    public function it_creates_dto_from_valid_data()
    {
        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
        ];

        $dto = CreateOrderDTO::fromRequest($data);

        $this->assertEquals(1, $dto->client_id);
        $this->assertEquals('sharpening', $dto->service_type);
        $this->assertEquals('manicure', $dto->tool_type);
        $this->assertEquals(1000, $dto->total_amount);
        $this->assertEquals('new', $dto->status);
        $this->assertEquals(1000, $dto->final_price);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client ID is required');

        $data = [
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
        ];

        CreateOrderDTO::fromRequest($data);
    }

    /** @test */
    public function it_validates_service_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid service type');

        $data = [
            'client_id' => 1,
            'service_type' => 'invalid',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
        ];

        CreateOrderDTO::fromRequest($data);
    }

    /** @test */
    public function it_validates_tool_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tool type');

        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'invalid',
            'total_amount' => 1000,
        ];

        CreateOrderDTO::fromRequest($data);
    }

    /** @test */
    public function it_validates_total_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total amount must be greater than 0');

        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 0,
        ];

        CreateOrderDTO::fromRequest($data);
    }

    /** @test */
    public function it_validates_delivery_address_when_needed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery address is required when delivery is needed');

        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
            'needs_delivery' => true,
        ];

        CreateOrderDTO::fromRequest($data);
    }

    /** @test */
    public function it_calculates_final_price_with_discount_percent()
    {
        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
            'discount_percent' => 10,
        ];

        $dto = CreateOrderDTO::fromRequest($data);

        $this->assertEquals(900, $dto->final_price);
    }

    /** @test */
    public function it_calculates_final_price_with_discount_amount()
    {
        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
            'discount_amount' => 100,
        ];

        $dto = CreateOrderDTO::fromRequest($data);

        $this->assertEquals(900, $dto->final_price);
    }

    /** @test */
    public function it_calculates_profit_when_cost_price_provided()
    {
        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
            'cost_price' => 600,
        ];

        $dto = CreateOrderDTO::fromRequest($data);

        $this->assertEquals(400, $dto->profit);
    }

    /** @test */
    public function it_returns_order_data_array()
    {
        $data = [
            'client_id' => 1,
            'service_type' => 'sharpening',
            'tool_type' => 'manicure',
            'total_amount' => 1000,
        ];

        $dto = CreateOrderDTO::fromRequest($data);
        $orderData = $dto->getOrderData();

        $this->assertIsArray($orderData);
        $this->assertEquals(1, $orderData['client_id']);
        $this->assertEquals('sharpening', $orderData['service_type']);
        $this->assertEquals('manicure', $orderData['tool_type']);
        $this->assertEquals(1000, $orderData['total_amount']);
    }
}
