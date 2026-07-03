<?php

namespace Database\Seeders;

use App\Application\ClientPortal\Command\RegisterClientCommand;
use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\RegisterClientHandler;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use Illuminate\Database\Seeder;

final class ClientPortalSeeder extends Seeder
{
    public const DEMO_CLIENT_PHONE = '+79001234567';

    public const DEMO_CLIENT_PASSWORD = 'password';

    public const DEMO_CLIENT_2_PHONE = '+79001234568';

    public const DEMO_CLIENT_3_PHONE = '+79001234569';

    public const DEMO_GUEST_PHONE = '+79009876543';

    /** Конвертируется в заказ (DemoOrderSeeder). */
    public const DEMO_LEAD_PHONE = '+79007778899';

    /** Остаётся в Filament → Лиды. */
    public const DEMO_LEAD_PHONE_2 = '+79007778801';

    public const DEMO_LEAD_PHONE_3 = '+79007778802';

    public function run(): void
    {
        $this->seedClient(
            phone: self::DEMO_CLIENT_PHONE,
            fullName: 'Анна Демонстра',
            email: 'demo.client@zatochka.local',
            deliveryAddress: 'г. Томск, ул. Кирова, 15',
        );

        $this->seedClient(
            phone: self::DEMO_CLIENT_2_PHONE,
            fullName: 'Борис Демонстра',
            email: 'demo.client2@zatochka.local',
            deliveryAddress: 'г. Томск, ул. Нахимова, 8',
        );

        $this->seedClient(
            phone: self::DEMO_CLIENT_3_PHONE,
            fullName: 'Виктория Демонстра',
            email: 'demo.client3@zatochka.local',
        );

        $this->seedLead(
            phone: self::DEMO_LEAD_PHONE,
            fullName: 'Елена Заявкина',
            serviceTypes: ['sharpening'],
            needsDelivery: true,
            deliveryAddress: 'г. Томск, пр. Ленина, 50',
            intakeData: [
                'tool_type' => 'manicure',
                'tools_count' => 6,
            ],
        );

        $this->seedLead(
            phone: self::DEMO_LEAD_PHONE_2,
            fullName: 'Павел Ремонтников',
            serviceTypes: ['repair'],
            intakeData: [
                'equipment_type' => 'clipper',
                'device_name' => 'Strong 2100',
                'problem_description' => 'Не включается, нужна диагностика',
                'urgency_type' => 'standard',
            ],
        );

        $this->seedLead(
            phone: self::DEMO_LEAD_PHONE_3,
            fullName: 'София Заточкина',
            serviceTypes: ['sharpening'],
            intakeData: [
                'tool_type' => 'hair',
                'tools_count' => 4,
            ],
        );
    }

    private function seedClient(
        string $phone,
        string $fullName,
        ?string $email = null,
        ?string $deliveryAddress = null,
    ): void {
        if (ClientModel::query()->where('phone', $phone)->exists()) {
            return;
        }

        app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            phone: $phone,
            fullName: $fullName,
            password: self::DEMO_CLIENT_PASSWORD,
            email: $email,
            deliveryAddress: $deliveryAddress,
        ));
    }

    /**
     * @param  list<string>  $serviceTypes
     * @param  array<string, mixed>|null  $intakeData
     */
    private function seedLead(
        string $phone,
        string $fullName,
        array $serviceTypes,
        ?string $comment = null,
        bool $needsDelivery = false,
        ?string $deliveryAddress = null,
        ?array $intakeData = null,
    ): void {
        if (SiteLeadModel::query()->where('phone', $phone)->exists()) {
            return;
        }

        app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
            fullName: $fullName,
            phone: $phone,
            serviceTypes: $serviceTypes,
            comment: $comment,
            intakeData: $intakeData,
            needsDelivery: $needsDelivery,
            deliveryAddress: $deliveryAddress,
        ));
    }
}
