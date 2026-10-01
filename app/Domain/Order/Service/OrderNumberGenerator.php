<?php

namespace App\Domain\Order\Service;

interface OrderNumberGenerator
{
    /**
     * Следующий номер вида ORD-YY-{NUM} для текущего года (NUM без padding).
     */
    public function next(?\DateTimeImmutable $at = null): string;
}
