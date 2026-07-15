<?php

namespace Tests\Feature\OrderFulfillment;

use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Domain\ClientPortal\Exception\SiteLeadPolicyViolation;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Filament\Support\LeadToOrderFormData;
use App\Filament\Support\OrderFormCommandBuilder;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Database\Seeders\DomainSeeder;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LeadConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_конвертация_лида_заточки_предзаполняет_инструменты(): void
    {
        $this->seed(DomainSeeder::class);

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
            fullName: 'Анна Клиент',
            phone: '+79001112299',
            serviceTypes: ['sharpening'],
            comment: 'Ножницы плохо режут',
            intakeData: [
                'tool_type' => 'manicure',
                'tools_count' => 2,
                'extra_comment' => 'Ножницы плохо режут',
            ],
            needsDelivery: true,
            deliveryAddress: 'ул. Ленина, 1',
        ));

        $lead = SiteLeadModel::query()->where('phone', '+79001112299')->firstOrFail();
        $formData = LeadToOrderFormData::fromLead($lead, $manager->id);

        $this->assertSame('sharpening', $formData['service_type']);
        $this->assertCount(1, $formData['tools']);
        $this->assertSame('manicure', $formData['tools'][0]['tool_type']);
        $this->assertSame(2, $formData['tools'][0]['quantity']);
        $this->assertSame('Ножницы плохо режут', $formData['problem_description']);

        $this->assertSame('new', $formData['client_mode']);

        $order = app(CreateOrderHandler::class)->handle(
            OrderFormCommandBuilder::buildCommand($formData)
        );

        $this->assertSame(OrderStatus::New, $order->status());
        $this->assertSame(OrderSource::SiteLead, $order->source());
        $this->assertNotNull($order->clientId());
        $this->assertCount(1, $order->tools());
        $this->assertSame('manicure', $order->tools()[0]->toolType);
        $this->assertSame(2, $order->tools()[0]->quantity);
    }

    public function test_конвертация_лида_ремонта_предзаполняет_оборудование(): void
    {
        $this->seed(DomainSeeder::class);

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
            fullName: 'Павел Ремонт',
            phone: '+79003334455',
            serviceTypes: ['repair'],
            intakeData: [
                'equipment_type' => 'clipper',
                'device_name' => 'Strong 2100',
                'problem_description' => 'Не включается после падения',
                'urgency_type' => 'urgent',
            ],
        ));

        $lead = SiteLeadModel::query()->where('phone', '+79003334455')->firstOrFail();
        $formData = LeadToOrderFormData::fromLead($lead, $manager->id);

        $this->assertSame('repair', $formData['service_type']);
        $this->assertSame('new', $formData['equipment_mode']);
        $this->assertSame('Strong 2100', $formData['equipment_name']);
        $this->assertSame('Машинка для стрижки', $formData['equipment_model']);
        $this->assertArrayNotHasKey('equipment_brand', $formData);
        $this->assertSame('Не включается после падения', $formData['problem_description']);
        $this->assertSame(OrderUrgency::Urgent->value, $formData['urgency']);

        $order = app(CreateOrderHandler::class)->handle(
            OrderFormCommandBuilder::buildCommand($formData)
        );

        $this->assertNotNull($order->clientId());
        $this->assertNotNull($order->equipmentId());
        $this->assertSame(OrderUrgency::Urgent, $order->urgency());
    }

    public function test_повторная_конвертация_лида_запрещена(): void
    {
        $this->seed(DomainSeeder::class);

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
            fullName: 'Пётр',
            phone: '+79002223344',
            serviceTypes: ['sharpening'],
            intakeData: [
                'tool_type' => 'hair',
                'tools_count' => 1,
            ],
        ));

        $lead = SiteLeadModel::query()->where('phone', '+79002223344')->firstOrFail();
        $formData = LeadToOrderFormData::fromLead($lead, $manager->id);

        $order = app(CreateOrderHandler::class)->handle(OrderFormCommandBuilder::buildCommand($formData));

        $clientId = $order->clientId();
        $this->assertNotNull($clientId);

        $this->expectException(SiteLeadPolicyViolation::class);

        app(CreateOrderHandler::class)->handle(OrderFormCommandBuilder::buildCommand([
            ...$formData,
            'client_mode' => 'existing',
            'client_id' => $clientId,
        ]));
    }

    public function test_лид_без_intake_data_сохраняет_legacy_поведение(): void
    {
        $lead = new SiteLeadModel([
            'full_name' => 'Test',
            'phone' => '+79000000000',
            'service_types' => ['sharpening'],
            'comment' => 'Старый лид',
            'needs_delivery' => false,
        ]);

        $data = LeadToOrderFormData::fromLead($lead);

        $this->assertSame('sharpening', $data['service_type']);
        $this->assertSame('Старый лид', $data['problem_description']);
        $this->assertArrayNotHasKey('tools', $data);
    }

    public function test_lead_to_order_form_data_берёт_первый_поддерживаемый_тип_услуги(): void
    {
        $lead = new SiteLeadModel([
            'full_name' => 'Test',
            'phone' => '+79000000001',
            'service_types' => ['repair', 'sharpening'],
            'comment' => 'Коммент',
            'intake_data' => [
                'device_name' => 'Device X',
                'problem_description' => 'Сломан',
            ],
            'needs_delivery' => false,
        ]);

        $data = LeadToOrderFormData::fromLead($lead);

        $this->assertSame('repair', $data['service_type']);
        $this->assertSame('Device X', $data['equipment_name']);
        $this->assertSame('Сломан', $data['problem_description']);
    }

    public function test_создание_ремонта_с_новым_оборудованием_и_серийниками(): void
    {
        $this->seed(DomainSeeder::class);

        $manager = UserModel::query()->where('email', IdentitySeeder::MANAGER_EMAIL)->firstOrFail();

        $order = app(CreateOrderHandler::class)->handle(
            OrderFormCommandBuilder::buildCommand([
                'service_type' => 'repair',
                'client_mode' => 'new',
                'client_full_name' => 'Иван Тест',
                'client_phone' => '+79005556677',
                'manager_id' => $manager->id,
                'equipment_mode' => 'new',
                'equipment_name' => 'Аппарат Strong 2100',
                'equipment_brand' => 'Strong',
                'equipment_model' => '2100',
                'equipment_serial_numbers' => [
                    ['component' => 'ручка', 'serial' => 'SN-HND-001'],
                    ['component' => 'блок питания', 'serial' => 'SN-PSU-001'],
                ],
            ]),
        );

        $this->assertNotNull($order->clientId());

        $equipmentId = $order->equipmentId();
        $this->assertNotNull($equipmentId);

        $equipment = app(EquipmentRepositoryInterface::class)->findById($equipmentId);

        $this->assertNotNull($equipment);
        $this->assertSame('Аппарат Strong 2100', $equipment->name());
        $this->assertSame('Strong', $equipment->brand());
        $this->assertSame('2100', $equipment->model());
        $this->assertSame([
            'ручка' => 'SN-HND-001',
            'блок питания' => 'SN-PSU-001',
        ], $equipment->serialNumbers());
    }
}
