<?php

namespace Tests\Feature\OrderFulfillment;

use App\Filament\Resources\SiteLeads\Pages\ListSiteLeads;
use App\Filament\Support\SiteLeadTableSearch;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SiteLeadTableSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_поиск_лидов_по_фио_без_учёта_регистра(): void
    {
        $this->seed();

        $query = SiteLeadModel::query()->where('converted', false);
        SiteLeadTableSearch::apply($query, 'павел');

        $this->assertSame(1, $query->count());
        $this->assertSame('Павел Ремонтников', $query->value('full_name'));
    }

    public function test_поиск_лидов_по_цифрам_телефона_без_форматирования(): void
    {
        $this->seed();

        SiteLeadModel::query()->create([
            'full_name' => 'Тест Телефон',
            'phone' => '+7 (983) 340-90-40',
            'service_types' => ['sharpening'],
            'intake_data' => ['tool_type' => 'manicure', 'tools_count' => 1],
            'needs_delivery' => false,
            'converted' => false,
        ]);

        $query = SiteLeadModel::query()->where('converted', false);
        SiteLeadTableSearch::apply($query, '9833409040');

        $this->assertSame(1, $query->count());
        $this->assertSame('Тест Телефон', $query->value('full_name'));
    }

    public function test_поиск_в_листинге_filament_находит_лид(): void
    {
        $this->seed();

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        Livewire::actingAs($manager)
            ->test(ListSiteLeads::class)
            ->set('tableSearch', 'павел')
            ->assertCountTableRecords(1);
    }
}
