<?php

namespace App\Console\Commands;

use App\Services\BonusService;
use Illuminate\Console\Command;

class ClearExpiredBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистить просроченные бонусы';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService)
    {
        $this->info('Начинаем очистку просроченных бонусов...');

        $clearedAmount = $bonusService->clearExpiredBonuses();

        if ($clearedAmount > 0) {
            $this->info("Очищено {$clearedAmount} просроченных бонусов");
        } else {
            $this->info('Просроченных бонусов не найдено');
        }

        $this->info('Очистка завершена!');
    }
}
