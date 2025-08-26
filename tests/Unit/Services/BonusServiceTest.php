<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\Order;
use App\Models\ClientBonus;
use App\Services\BonusService;
use App\Models\BonusSetting;

class BonusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Сеем базовую конфигурацию
        \Database\Seeders\BonusSettingSeeder::class;
    }

    public function test_award_bonus_for_order_creates_balance_and_transaction(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id, 'status' => 'closed', 'final_price' => 10000]);

        $service = new BonusService();
        $service->awardBonusForOrder($order);

        $bonus = $client->bonus()->first();
        $this->assertNotNull($bonus);
        $this->assertGreaterThan(0, (float) $bonus->balance);
    }

    public function test_calculate_max_bonus_spend(): void
    {
        $client = Client::factory()->create();
        $service = new BonusService();
        $bonus = $service->getOrCreateClientBonus($client);
        $bonus->update(['balance' => 10000]);

        $order = Order::factory()->create(['client_id' => $client->id, 'final_price' => 8000]);

        $max = $service->calculateMaxBonusSpend($order);
        $this->assertEquals(4000, $max); // 50% от 8000
    }
}
