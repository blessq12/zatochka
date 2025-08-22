<?php

namespace App\Console\Commands;

use App\Contracts\TelegramWebhookServiceContract;
use Illuminate\Console\Command;

class TelegramWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook
                            {action : Action to perform (set|info|delete|test)}
                            {--url= : Webhook URL for set action}
                            {--chat-id= : Chat ID for test message}
                            {--message= : Message for test action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Telegram webhook';

    protected TelegramWebhookServiceContract $webhookService;

    public function __construct(TelegramWebhookServiceContract $webhookService)
    {
        parent::__construct();
        $this->webhookService = $webhookService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'set':
                return $this->setWebhook();
            case 'info':
                return $this->getWebhookInfo();
            case 'delete':
                return $this->deleteWebhook();
            case 'test':
                return $this->sendTestMessage();
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: set, info, delete, test');
                return 1;
        }
    }

    /**
     * Set webhook
     */
    protected function setWebhook(): int
    {
        $url = $this->option('url');

        if (!$url) {
            $this->error('Webhook URL is required. Use --url option.');
            return 1;
        }

        $this->info("Setting webhook to: {$url}");

        $result = $this->webhookService->setWebhook($url);

        if ($result['success']) {
            $this->info('✅ Webhook установлен успешно!');
            $this->line('Response: ' . json_encode($result['data'], JSON_PRETTY_PRINT));
            return 0;
        } else {
            $this->error('❌ Ошибка установки webhook: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Get webhook info
     */
    protected function getWebhookInfo(): int
    {
        $this->info('Getting webhook info...');

        $result = $this->webhookService->getWebhookInfo();

        if ($result['success']) {
            $this->info('✅ Информация о webhook получена:');
            $this->line(json_encode($result['data'], JSON_PRETTY_PRINT));
            return 0;
        } else {
            $this->error('❌ Ошибка получения информации: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Delete webhook
     */
    protected function deleteWebhook(): int
    {
        if (!$this->confirm('Are you sure you want to delete the webhook?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Deleting webhook...');

        $result = $this->webhookService->deleteWebhook();

        if ($result['success']) {
            $this->info('✅ Webhook удален успешно!');
            return 0;
        } else {
            $this->error('❌ Ошибка удаления webhook: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Send test message
     */
    protected function sendTestMessage(): int
    {
        $chatId = $this->option('chat-id');
        $message = $this->option('message');

        if (!$chatId) {
            $this->error('Chat ID is required. Use --chat-id option.');
            return 1;
        }

        if (!$message) {
            $message = '🧪 Тестовое сообщение от бота Заточка ТСК!';
        }

        $this->info("Sending test message to chat {$chatId}...");

        $success = $this->webhookService->sendTestMessage($chatId, $message);

        if ($success) {
            $this->info('✅ Тестовое сообщение отправлено успешно!');
            return 0;
        } else {
            $this->error('❌ Ошибка отправки тестового сообщения');
            return 1;
        }
    }
}
