<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\Order;
use App\Models\BonusTransaction;
use Laravel\Sanctum\Sanctum;

class BonusEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_balance_requires_auth(): void
    {
        $response = $this->getJson('/api/client/bonus/balance');
        $response->assertStatus(401);
    }

    public function test_get_balance_ok(): void
    {
        $client = Client::factory()->create();
        Sanctum::actingAs($client, ['*']);

        $response = $this->getJson('/api/client/bonus/balance');
        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_calc_max_ok(): void
    {
        $client = Client::factory()->create();
        Sanctum::actingAs($client, ['*']);
        $order = Order::factory()->create(['client_id' => $client->id, 'final_price' => 8000]);

        $response = $this->getJson('/api/client/bonus/calc-max?order_id=' . $order->id);
        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_transactions_pagination(): void
    {
        $client = Client::factory()->create();
        Sanctum::actingAs($client, ['*']);

        // seed 30 transactions
        BonusTransaction::factory()->count(30)->create([
            'client_id' => $client->id,
            'type' => 'earn',
            'amount' => 100,
            'description' => 'test',
        ]);

        $response = $this->getJson('/api/client/bonus/transactions?per_page=10');
        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.total', 30);
    }
}
