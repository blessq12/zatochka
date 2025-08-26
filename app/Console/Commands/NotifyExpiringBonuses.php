<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClientBonus;
use App\Events\Bonus\BonusExpiring;
use Carbon\Carbon;

class NotifyExpiringBonuses extends Command
{
    protected $signature = 'bonuses:notify-expiring {--days=7 : За сколько дней предупреждать об истечении}';

    protected $description = 'Отправить клиентам уведомления о скором истечении бонусов';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDateStart = Carbon::now()->addDays($days)->startOfDay();
        $targetDateEnd = Carbon::now()->addDays($days)->endOfDay();

        $this->info("Ищем бонусы, истекающие через {$days} дней...");

        $bonuses = ClientBonus::with('client')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$targetDateStart, $targetDateEnd])
            ->where('balance', '>', 0)
            ->get();

        $count = 0;
        foreach ($bonuses as $bonus) {
            event(new BonusExpiring($bonus->client, (float) $bonus->balance, $days));
            $count++;
        }

        $this->info("Отправлено уведомлений: {$count}");
        return self::SUCCESS;
    }
}
