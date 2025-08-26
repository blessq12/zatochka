<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BonusService;
use App\Models\Client;
use App\Models\Order;
use App\Models\ClientBonus;
use App\Models\BonusTransaction;
use App\Models\BonusSetting;
use Database\Seeders\BonusSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class BonusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BonusService $bonusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bonusService = new BonusService();

        // Запускаем сидер настроек
        $this->seed(BonusSettingSeeder::class);

        // Отключаем события для тестов
        Event::fake();
    }

    public function test_can_award_bonus_for_order()
    {
        // Создаем клиента и заказ
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 2000, // Больше минимальной суммы 1500
        ]);

        // Начисляем бонусы
        $this->bonusService->awardBonusForOrder($order);

        // Проверяем, что бонусы начислены
        $clientBonus = $client->bonus;
        $this->assertNotNull($clientBonus);
        $this->assertEquals(100, $clientBonus->balance); // 5% от 2000 = 100

        // Проверяем транзакцию
        $transaction = BonusTransaction::where('client_id', $client->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('earn', $transaction->type);
        $this->assertEquals(100, $transaction->amount);
    }

    public function test_cannot_award_bonus_for_small_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 1000, // Меньше минимальной суммы 1500
        ]);

        $this->bonusService->awardBonusForOrder($order);

        $clientBonus = $client->bonus;
        $this->assertNull($clientBonus); // Бонусы не должны начисляться
    }

    public function test_can_spend_bonus_for_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 5000, // Больше минимальной суммы для списания 3000
        ]);

        // Создаем бонусы клиенту
        $clientBonus = $this->bonusService->getOrCreateClientBonus($client);
        $clientBonus->update(['balance' => 1000]);

        // Списываем бонусы
        $result = $this->bonusService->spendBonusForOrder($order, 500);

        $this->assertTrue($result);

        $clientBonus->refresh();
        $this->assertEquals(500, $clientBonus->balance); // 1000 - 500 = 500

        // Проверяем транзакцию
        $transaction = BonusTransaction::where('client_id', $client->id)
            ->where('type', 'spend')
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(500, $transaction->amount);
    }

    public function test_cannot_spend_more_than_available()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 5000,
        ]);

        // Создаем бонусы клиенту
        $clientBonus = $this->bonusService->getOrCreateClientBonus($client);
        $clientBonus->update(['balance' => 100]);

        // Пытаемся списать больше, чем есть
        $result = $this->bonusService->spendBonusForOrder($order, 500);

        $this->assertFalse($result);

        $clientBonus->refresh();
        $this->assertEquals(100, $clientBonus->balance); // Баланс не изменился
    }

    public function test_cannot_spend_bonus_for_small_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 2000, // Меньше минимальной суммы для списания 3000
        ]);

        $clientBonus = $this->bonusService->getOrCreateClientBonus($client);
        $clientBonus->update(['balance' => 1000]);

        $result = $this->bonusService->spendBonusForOrder($order, 500);

        $this->assertFalse($result);
    }

    public function test_can_award_birthday_bonus()
    {
        $client = Client::factory()->create([
            'birth_date' => now()->format('Y-m-d'),
        ]);

        $this->bonusService->awardBirthdayBonus($client);

        $clientBonus = $client->bonus;
        $this->assertNotNull($clientBonus);
        $this->assertEquals(1000, $clientBonus->balance); // Бонус за день рождения

        $transaction = BonusTransaction::where('client_id', $client->id)
            ->where('type', 'earn')
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(1000, $transaction->amount);
    }

    public function test_can_award_first_review_bonus()
    {
        $client = Client::factory()->create();

        $this->bonusService->awardFirstReviewBonus($client);

        $clientBonus = $client->bonus;
        $this->assertNotNull($clientBonus);
        $this->assertEquals(1000, $clientBonus->balance); // Бонус за первый отзыв
    }

    public function test_cannot_award_first_review_bonus_twice()
    {
        $client = Client::factory()->create();

        // Первый раз
        $this->bonusService->awardFirstReviewBonus($client);

        // Второй раз
        $this->bonusService->awardFirstReviewBonus($client);

        $clientBonus = $client->bonus;
        $this->assertEquals(1000, $clientBonus->balance); // Только один бонус
    }

    public function test_calculate_max_bonus_spend()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'final_price' => 10000,
        ]);

        // Создаем бонусы клиенту
        $clientBonus = $this->bonusService->getOrCreateClientBonus($client);
        $clientBonus->update(['balance' => 10000]);

        $maxSpend = $this->bonusService->calculateMaxBonusSpend($order);

        // Максимум 50% от суммы заказа = 5000
        $this->assertEquals(5000, $maxSpend);
    }
}
