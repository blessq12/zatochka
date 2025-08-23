<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartAllProcesses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processes:start-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает все необходимые процессы artisan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Запускаем все процессы...');

        $php_path = trim(shell_exec('which php'));
        $artisan_path = base_path('artisan');

        // Проверяем и запускаем queue:work
        $queueProcesses = shell_exec('ps aux | grep "artisan queue:work" | grep -v grep | wc -l');
        $queueProcesses = (int) trim($queueProcesses);

        if ($queueProcesses == 0) {
            $this->info('Запуск очереди...');
            shell_exec("nohup {$php_path} {$artisan_path} queue:work --daemon > /dev/null 2>&1 &");
            $this->info('Очередь запущена.');
        } else {
            $this->info("Очередь уже запущена ({$queueProcesses} процессов).");
        }

        // Проверяем и запускаем schedule:run
        $scheduleProcesses = shell_exec('ps aux | grep "artisan schedule:run" | grep -v grep | wc -l');
        $scheduleProcesses = (int) trim($scheduleProcesses);

        if ($scheduleProcesses == 0) {
            $this->info('Запуск планировщика...');
            shell_exec("nohup {$php_path} {$artisan_path} schedule:run > /dev/null 2>&1 &");
            $this->info('Планировщик запущен.');
        } else {
            $this->info("Планировщик уже запущен ({$scheduleProcesses} процессов).");
        }

        $this->info('Все процессы запущены.');
    }
}
