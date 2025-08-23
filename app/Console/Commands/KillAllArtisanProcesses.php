<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KillAllArtisanProcesses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processes:kill-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Убивает все процессы artisan (queue:work, schedule:run)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Убиваем все процессы artisan...');

        // Убиваем все процессы queue:work
        $queuePids = shell_exec('ps aux | grep "artisan queue:work" | grep -v grep | awk \'{print $2}\'');
        if (!empty(trim($queuePids))) {
            $pids = explode("\n", trim($queuePids));
            foreach ($pids as $pid) {
                if (!empty($pid)) {
                    shell_exec("kill -9 {$pid}");
                    $this->info("Убит процесс queue:work с PID: {$pid}");
                }
            }
        }

        // Убиваем все процессы schedule:run
        $schedulePids = shell_exec('ps aux | grep "artisan schedule:run" | grep -v grep | awk \'{print $2}\'');
        if (!empty(trim($schedulePids))) {
            $pids = explode("\n", trim($schedulePids));
            foreach ($pids as $pid) {
                if (!empty($pid)) {
                    shell_exec("kill -9 {$pid}");
                    $this->info("Убит процесс schedule:run с PID: {$pid}");
                }
            }
        }

        // Убиваем все процессы queue:check-n-restart
        $checkPids = shell_exec('ps aux | grep "artisan queue:check-n-restart" | grep -v grep | awk \'{print $2}\'');
        if (!empty(trim($checkPids))) {
            $pids = explode("\n", trim($checkPids));
            foreach ($pids as $pid) {
                if (!empty($pid)) {
                    shell_exec("kill -9 {$pid}");
                    $this->info("Убит процесс queue:check-n-restart с PID: {$pid}");
                }
            }
        }

        $this->info('Все процессы artisan убиты.');
    }
}
