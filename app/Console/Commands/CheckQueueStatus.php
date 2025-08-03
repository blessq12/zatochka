<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckQueueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:check-n-restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяем статус очереди и перезапускаем если необходимо';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = shell_exec('ps aux | grep "artisan queue:work" | grep -v grep | wc -l');
        $php_path = shell_exec('which php');

        if ($status > 1) {
            $this->info('Очередь уже запущена.');
        } else {
            $this->info('Запуск очереди...');
            shell_exec($php_path . ' /var/www/u3161183/data/www/app/artisan queue:work');
            $this->info('Очередь запущена.');
        }
    }
}
