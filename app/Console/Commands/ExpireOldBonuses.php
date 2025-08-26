<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BonusService;

class ExpireOldBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Списать просроченные бонусы клиентов';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService)
    {
        $this->info('Начинаем списание просроченных бонусов...');

        $expiredAmount = $bonusService->expireOldBonuses();

        if ($expiredAmount > 0) {
            $this->info("Списано просроченных бонусов на сумму: " . number_format($expiredAmount, 0) . " рублей");
        } else {
            $this->info('Просроченных бонусов не найдено');
        }

        $this->info('Списание завершено!');
    }
}
