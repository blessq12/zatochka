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

    public function test_step_two_shows_existing_client_select_by_default(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Sharpening->value,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'client_mode' => 'existing',
            ])
            ->goToNextWizardStep()
            ->assertWizardCurrentStep(2)
            ->assertFormSet(['client_mode' => 'existing'])
            ->assertFormFieldIsVisible('client_picker')
            ->assertFormFieldIsHidden('new_client_name')
            ->assertFormComponentActionDoesNotExist('client_picker', 'createOption');
    }

    public function test_step_two_can_register_new_client_via_explicit_mode(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Sharpening->value,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'client_mode' => 'new',
                'new_client_name' => 'Иван Новый',
                'new_client_phone' => '+7 (999) 111-22-33',
                'new_client_email' => 'ivan@example.test',
            ])
            ->goToNextWizardStep()
            ->assertWizardCurrentStep(2)
            ->goToNextWizardStep()
            ->assertHasNoFormErrors()
            ->assertWizardCurrentStep(3);

        $client = ClientModel::query()->where('phone', '+7 (999) 111-22-33')->first();

        $this->assertNotNull($client);
        $this->assertSame('Иван Новый', $client->name);
    }

    public function test_step_three_repair_registers_new_equipment_via_explicit_mode(): void
    {
        $this->actingAs($this->manager());

        $clientId = $this->registerClient('Существующий', '+79000000001');

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Repair->value,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'client_mode' => 'existing',
                'client_id' => $clientId,
                'client_picker' => $clientId,
                'equipment_mode' => 'new',
                'new_equipment_title' => 'Газонокосилка',
                'new_equipment_type' => 'other',
                'new_equipment_brand' => 'Honda',
                'new_equipment_model_name' => 'HRX',
                'new_equipment_parts' => [
                    ['name' => 'Нож', 'serialNumber' => 'SN-42'],
                ],
            ])
            ->goToNextWizardStep()
            ->assertWizardCurrentStep(2)
            ->assertFormComponentActionDoesNotExist('equipment_picker', 'createOption')
            ->goToNextWizardStep()
            ->assertWizardCurrentStep(3)
            ->goToNextWizardStep()
            ->assertHasNoFormErrors()
            ->assertWizardCurrentStep(4);

        $equipment = ClientEquipmentModel::query()
            ->where('client_id', $clientId)
            ->where('title', 'Газонокосилка')
            ->first();

        $this->assertNotNull($equipment);
        $this->assertSame('Honda', $equipment->brand);
        $this->assertSame('HRX', $equipment->model_name);
        $this->assertSame('other', $equipment->equipment_type);
    }

    public function test_repair_order_created_with_fresh_client_and_equipment(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'service_type' => OrderServiceType::Repair->value,
                'billing_type' => 'paid',
                'urgency' => 'normal',
                'client_mode' => 'new',
                'new_client_name' => 'Клиент Ремонт',
                'new_client_phone' => '+7 (900) 111-22-33',
                'equipment_mode' => 'new',
                'new_equipment_title' => 'Триммер',
                'new_equipment_type' => 'trimmer',
                'new_equipment_brand' => 'Stihl',
                'new_equipment_model_name' => 'FS 55',
                'new_equipment_parts' => [
                    ['name' => 'Голова', 'serialNumber' => null],
                ],
                'estimated_amount' => '1500',
                'delivery_required' => false,
            ])
            ->goToNextWizardStep()
            ->goToNextWizardStep()
            ->goToNextWizardStep()
            ->call('create')
            ->assertHasNoFormErrors();

        $clientId = (int) ClientModel::query()->where('phone', '+7 (900) 111-22-33')->value('id');
        $this->assertGreaterThan(0, $clientId);

        $equipmentId = (int) ClientEquipmentModel::query()
            ->where('client_id', $clientId)
            ->where('title', 'Триммер')
            ->value('id');
        $this->assertGreaterThan(0, $equipmentId);

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
