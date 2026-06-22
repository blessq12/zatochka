<?php

namespace App\Filament\Support;

use App\Domain\ClientPortal\Enum\ReviewStatus;

final class ClientReviewPresenter
{
    public static function statusLabel(ReviewStatus $status): string
    {
        return match ($status) {
            ReviewStatus::Pending => 'На модерации',
            ReviewStatus::Approved => 'Опубликован',
            ReviewStatus::Rejected => 'Не опубликован',
        };
    }

    public static function statusColor(ReviewStatus $status): string
    {
        return match ($status) {
            ReviewStatus::Pending => 'warning',
            ReviewStatus::Approved => 'success',
            ReviewStatus::Rejected => 'danger',
        };
    }
}
