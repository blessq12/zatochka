<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BonusService;
use App\Models\Client;
use Carbon\Carbon;

class AwardBirthdayBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Начислить бонусы клиентам за день рождения';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService)
    {
        $this->info('Начинаем начисление бонусов за день рождения...');

        $today = Carbon::today();
        $clientsWithBirthday = Client::whereNotNull('birth_date')
            ->whereRaw('DATE_FORMAT(birth_date, "%m-%d") = ?', [$today->format('m-d')])
            ->get();

        $this->info("Найдено клиентов с днем рождения: {$clientsWithBirthday->count()}");

        $awardedCount = 0;
        foreach ($clientsWithBirthday as $client) {
            try {
                $bonusService->awardBirthdayBonus($client);
                $awardedCount++;
                $this->line("Начислены бонусы клиенту: {$client->full_name}");
            } catch (\Exception $e) {
                $this->error("Ошибка при начислении бонусов клиенту {$client->full_name}: " . $e->getMessage());
            }
        }

        $this->info("Бонусы начислены {$awardedCount} клиентам");
        $this->info('Начисление завершено!');
    }
}
