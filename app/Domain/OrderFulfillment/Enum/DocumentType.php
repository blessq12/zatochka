<?php

namespace App\Domain\OrderFulfillment\Enum;

enum DocumentType: string
{
    case Receipt = 'receipt';
    case HandoverAct = 'handover_act';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Квитанция о приёме',
            self::HandoverAct => 'Акт выдачи',
        };
    }

    public function viewName(): string
    {
        return match ($this) {
            self::Receipt => 'documents.acceptance',
            self::HandoverAct => 'documents.issuance',
        };
    }
}
