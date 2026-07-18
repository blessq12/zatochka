<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Support;

use App\Domain\Feedback\VO\ReviewStatus;
use App\Domain\Order\VO\OrderNumber;
use App\Infrastructure\Feedback\Model\ReviewModel;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Support\View\Concerns\CanGenerateIconButtonHtml;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;

use function Filament\Support\generate_icon_html;

final class ReviewPresentation
{
    public static function orderNumberLabel(ReviewModel $record): string
    {
        $number = $record->order?->number;

        if (! filled($number)) {
            return (string) $record->order_id;
        }

        try {
            return (string) new OrderNumber((string) $number);
        } catch (\Throwable) {
            return (string) $number;
        }
    }

    public static function clientName(ReviewModel $record): string
    {
        return filled($record->client?->name)
            ? (string) $record->client->name
            : 'Без имени';
    }

    public static function clientPhone(ReviewModel $record): string
    {
        return filled($record->client?->phone)
            ? (string) $record->client->phone
            : '—';
    }

    public static function ratingStarsHtml(int $rating): HtmlString
    {
        $rating = max(0, min(5, $rating));
        $parts = [];

        for ($i = 1; $i <= 5; $i++) {
            $icon = $i <= $rating ? Heroicon::Star : Heroicon::OutlinedStar;
            $colorClass = $i <= $rating
                ? 'text-warning-500 dark:text-warning-400'
                : 'text-gray-300 dark:text-gray-600';

            $parts[] = generate_icon_html(
                $icon,
                attributes: (new ComponentAttributeBag)->class([
                    'h-4 w-4',
                    $colorClass,
                ]),
                size: IconSize::Small,
            )?->toHtml() ?? '';
        }

        return new HtmlString(
            '<div class="fi-ta-icon" style="display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;gap:0.125rem;padding:0;width:auto;" title="'
            .e($rating.'/5')
            .'">'
            .implode('', $parts)
            .'</div>'
        );
    }

    public static function listingFlagsHtml(ReviewModel $record): HtmlString
    {
        $hasReply = self::hasManagerReply($record);
        $status = (string) $record->status;

        $flags = [
            [
                'icon' => match ($status) {
                    ReviewStatus::Published->value => Heroicon::OutlinedCheckBadge,
                    ReviewStatus::Rejected->value => Heroicon::OutlinedXMark,
                    ReviewStatus::PendingModeration->value => Heroicon::OutlinedClock,
                    default => Heroicon::OutlinedQuestionMarkCircle,
                },
                'tooltip' => match ($status) {
                    ReviewStatus::Published->value => 'Принят',
                    ReviewStatus::Rejected->value => 'Отклонён',
                    ReviewStatus::PendingModeration->value => 'На модерации',
                    default => self::statusLabel($status),
                },
                'color' => self::statusColor($status),
            ],
            [
                'icon' => $hasReply
                    ? Heroicon::OutlinedChatBubbleLeftRight
                    : Heroicon::OutlinedChatBubbleBottomCenterText,
                'tooltip' => $hasReply ? 'Есть ответ' : 'Нет ответа',
                'color' => $hasReply ? 'info' : 'gray',
            ],
        ];

        $renderer = new class
        {
            use CanGenerateIconButtonHtml;

            public function render(
                Heroicon $icon,
                string $tooltip,
                string $color,
            ): string {
                return $this->generateIconButtonHtml(
                    attributes: new ComponentAttributeBag([
                        'type' => 'button',
                        'tabindex' => '-1',
                    ]),
                    color: $color,
                    hasLoadingIndicator: false,
                    icon: $icon,
                    label: $tooltip,
                    tag: 'button',
                    tooltip: $tooltip,
                    type: 'button',
                );
            }
        };

        $parts = array_map(
            static fn (array $flag): string => $renderer->render(
                $flag['icon'],
                $flag['tooltip'],
                $flag['color'],
            ),
            $flags,
        );

        return new HtmlString(
            '<div class="fi-ta-icon fi-align-center" style="display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;justify-content:center;gap:0.375rem;padding:0;width:auto;">'
            .implode('', $parts)
            .'</div>'
        );
    }

    public static function hasManagerReply(ReviewModel $record): bool
    {
        return trim((string) ($record->manager_reply ?? '')) !== '';
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            ReviewStatus::PendingModeration->value => 'На модерации',
            ReviewStatus::Published->value => 'Принят',
            ReviewStatus::Rejected->value => 'Отклонён',
            ReviewStatus::Hidden->value => 'Скрыт',
            ReviewStatus::Deleted->value => 'Удалён',
            default => $status ?: '—',
        };
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            ReviewStatus::PendingModeration->value => 'warning',
            ReviewStatus::Published->value => 'success',
            ReviewStatus::Rejected->value => 'danger',
            ReviewStatus::Hidden->value => 'gray',
            ReviewStatus::Deleted->value => 'danger',
            default => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function listingStatusFilterOptions(): array
    {
        return [
            ReviewStatus::PendingModeration->value => 'На модерации',
            ReviewStatus::Published->value => 'Принятые',
            ReviewStatus::Rejected->value => 'Отклонённые',
        ];
    }
}
