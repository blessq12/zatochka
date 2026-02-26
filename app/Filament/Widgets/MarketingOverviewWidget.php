<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketingOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Маркетинг: источники и каналы';
    }

    protected function getStats(): array
    {
        $stats = [];

        $clientsQuery = Client::query()->where('is_deleted', false);

        $totalClients = (clone $clientsQuery)->count();

        // Статистика по источникам (marketing_source)
        $sources = Order::getAvailableClientSources();

        foreach ($sources as $key => $label) {
            $count = (clone $clientsQuery)
                ->where('marketing_source', $key)
                ->count();

            $percent = $totalClients > 0 ? round(($count / $totalClients) * 100) : 0;

            $stats[] = Stat::make("Источник: {$label}", $count)
                ->description("{$percent}% от клиентов")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary');
        }

        // Прочие / не указан
        $otherCount = (clone $clientsQuery)
            ->whereNull('marketing_source')
            ->orWhereNotIn('marketing_source', array_keys($sources))
            ->count();

        if ($otherCount > 0) {
            $otherPercent = $totalClients > 0 ? round(($otherCount / $totalClients) * 100) : 0;

            $stats[] = Stat::make('Источник: другое/не указано', $otherCount)
                ->description("{$otherPercent}% от клиентов")
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('gray');
        }

        // Статистика по каналу первого контакта
        $channels = [
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'instagram' => 'Instagram',
            'phone' => 'Телефон',
            'offline' => 'Оффлайн',
            'other' => 'Другое',
        ];

        foreach ($channels as $key => $label) {
            $count = (clone $clientsQuery)
                ->where('first_contact_channel', $key)
                ->count();

            if ($count === 0) {
                continue;
            }

            $percent = $totalClients > 0 ? round(($count / $totalClients) * 100) : 0;

            $stats[] = Stat::make("Канал: {$label}", $count)
                ->description("{$percent}% от клиентов")
                ->descriptionIcon('heroicon-m-share')
                ->color('info');
        }

        return $stats;
    }
}

