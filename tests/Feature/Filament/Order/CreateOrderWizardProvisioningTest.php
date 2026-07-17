<?php

namespace Tests\Feature\Filament\Order;

use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Pages\CreateOrder;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class CreateOrderWizardProvisioningTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_step_two_can_register_new_client_via_create_option(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateOrder::class)
            ->assertFormComponentActionExists('client_id', 'createOption')
            ->callFormComponentAction('client_id', 'createOption', data: [
                'name' => 'Иван Новый',
                'phone' => '+7 (999) 111-22-33',
                'email' => 'ivan@example.test',
            ])
            ->assertHasNoFormErrors();

        $client = ClientModel::query()->where('phone', '+7 (999) 111-22-33')->first();

        $this->assertNotNull($client);
        $this->assertSame('Иван Новый', $client->name);

        Livewire::test(CreateOrder::class)
            ->callFormComponentAction('client_id', 'createOption', data: [
                'name' => 'Пётр Новый',
                'phone' => '+7 (999) 444-55-66',
            ])
            ->assertFormSet([
                'client_id' => (int) ClientModel::query()->where('phone', '+7 (999) 444-55-66')->value('id'),
            ]);
    }

    public function test_step_three_repair_can_register_new_equipment_via_create_option(): void
    {
        $this->actingAs($this->manager());

        $clientId = $this->registerClient('Существующий', '+79000000001');

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Repair->value,
                'client_id' => $clientId,
            ])
            ->assertFormComponentActionExists('client_equipment_ids', 'createOption')
            ->callFormComponentAction('client_equipment_ids', 'createOption', data: [
                'title' => 'Газонокосилка',
                'brand' => 'Honda',
                'model_name' => 'HRX',
                'notes' => 'тест',
                'parts' => [
                    ['name' => 'Нож', 'serialNumber' => 'SN-42'],
                ],
            ])
            ->assertHasNoFormErrors();

        $equipment = ClientEquipmentModel::query()
            ->where('client_id', $clientId)
            ->where('title', 'Газонокосилка')
            ->first();

        $this->assertNotNull($equipment);
        $this->assertSame('Honda', $equipment->brand);
        $this->assertSame('HRX', $equipment->model_name);
    }

    public function test_repair_order_created_with_fresh_client_and_equipment(): void
    {
        $this->actingAs($this->manager());

        $component = Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Repair->value,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'estimated_amount' => '1500',
                'delivery_required' => false,
            ])
            ->callFormComponentAction('client_id', 'createOption', data: [
                'name' => 'Клиент Ремонт',
                'phone' => '+7 (900) 111-22-33',
            ]);

        $clientId = (int) ClientModel::query()->where('phone', '+7 (900) 111-22-33')->value('id');
        $this->assertGreaterThan(0, $clientId);

        $component
            ->fillForm(['client_id' => $clientId])
            ->callFormComponentAction('client_equipment_ids', 'createOption', data: [
                'title' => 'Триммер',
                'brand' => 'Stihl',
                'model_name' => 'FS 55',
                'parts' => [
                    ['name' => 'Голова', 'serialNumber' => null],
                ],
            ]);

        $equipmentId = (int) ClientEquipmentModel::query()
            ->where('client_id', $clientId)
            ->where('title', 'Триммер')
            ->value('id');
        $this->assertGreaterThan(0, $equipmentId);

        $component
            ->fillForm([
                'client_equipment_ids' => [$equipmentId],
                'estimated_amount' => '1500',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = OrderModel::query()->latest('created_at')->first();

        $this->assertNotNull($order);
        $this->assertSame(OrderServiceType::Repair->value, $order->service_type);
        $this->assertSame($clientId, (int) $order->client_id);
        $this->assertSame(OrderStatus::Created->value, $order->status);
        $this->assertTrue(
            $order->items()->where('client_equipment_id', $equipmentId)->exists(),
        );
    }

    private function manager(): User
    {
        return User::query()->create([
            'name' => 'Manager',
            'email' => 'manager@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
        ]);
    }
}
