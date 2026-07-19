<?php

namespace App\Domain\Documents\VO;

enum PdfTemplateKind: string
{
    case ReceptionReceipt = 'reception_receipt';
    case IssueAct = 'issue_act';

    public function label(): string
    {
        return match ($this) {
            self::ReceptionReceipt => 'Квитанция приёмки',
            self::IssueAct => 'Акт выдачи',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }
}
