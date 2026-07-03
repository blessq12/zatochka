<?php

namespace Tests\Feature\OrderFulfillment;

use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Support\OrderTableSearch;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrderTableSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_поиск_заказов_по_номеру(): void
    {
        $this->seed();

        $query = OrderModel::query();
        OrderTableSearch::apply($query, 'ORD-2026-0001');

        $this->assertSame(1, $query->count());
        $this->assertSame('ORD-2026-0001', $query->value('order_number'));
    }

    public function test_поиск_заказов_по_фио_без_учёта_регистра(): void
    {
        $this->seed();

        $query = OrderModel::query();
        OrderTableSearch::apply($query, 'ольга');

        $this->assertSame(1, $query->count());
        $this->assertSame('Ольга Петрова', $query->value('client_snapshot')['full_name']);
    }

    public function test_поиск_заказов_по_цифрам_телефона(): void
    {
        $this->seed();

        $query = OrderModel::query();
        OrderTableSearch::apply($query, '9001110001');

        $this->assertSame(1, $query->count());
        $this->assertSame('+79001110001', $query->value('client_snapshot')['phone']);
    }

    public function test_фильтр_заказов_по_типу_услуги(): void
    {
        $this->seed();

        $sharpeningCount = OrderModel::query()
            ->whereJsonContains('service_types', 'sharpening')
            ->count();

        $repairCount = OrderModel::query()
            ->whereJsonContains('service_types', 'repair')
            ->count();

        $this->assertGreaterThan(0, $sharpeningCount);
        $this->assertGreaterThan(0, $repairCount);
    }

    public function test_фильтр_заказов_по_мастеру(): void
    {
        $this->seed();

        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        $count = OrderModel::query()
            ->where('master_id', $master->id)
            ->count();

        $this->assertGreaterThan(0, $count);
    }

    public function test_поиск_и_фильтры_в_листинге_filament(): void
    {
        $this->seed();

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();
        $master = UserModel::query()->where('email', IdentitySeeder::MASTER_EMAIL)->firstOrFail();

        Livewire::actingAs($manager)
            ->test(ListOrders::class)
            ->set('tableSearch', 'ольга')
            ->assertCountTableRecords(1)
            ->set('tableFilters.service_type.value', 'sharpening')
            ->assertCountTableRecords(1)
            ->set('tableFilters.master_id.value', (string) $master->id)
            ->assertCountTableRecords(1)
            ->assertCanSeeTableRecords(
                OrderModel::query()
                    ->where('order_number', 'ORD-2026-0001')
                    ->where('status', OrderStatus::New)
                    ->get()
            );
    }
}
