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

    public const DEMO_GUEST_PHONE = '+79009876543';

    public const DEMO_LEAD_PHONE = '+79007778899';

    public function run(): void
    {
        if (ClientModel::query()->where('phone', self::DEMO_CLIENT_PHONE)->doesntExist()) {
            app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
                phone: self::DEMO_CLIENT_PHONE,
                fullName: 'Анна Демонстра',
                password: self::DEMO_CLIENT_PASSWORD,
                email: 'demo.client@zatochka.local',
                deliveryAddress: 'г. Томск, ул. Кирова, 15',
            ));
        }

        if (SiteLeadModel::query()->where('phone', self::DEMO_LEAD_PHONE)->doesntExist()) {
            app(SubmitSiteLeadHandler::class)->handle(new SubmitSiteLeadCommand(
                fullName: 'Елена Заявкина',
                phone: self::DEMO_LEAD_PHONE,
                serviceTypes: ['sharpening'],
                comment: '6 маникюрных кусачек, нужна доставка',
                needsDelivery: true,
                deliveryAddress: 'г. Томск, пр. Ленина, 50',
            ));
        }
    }
}
