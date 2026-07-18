<?php

namespace Tests\Unit\Domain\Finance;

use App\Domain\Finance\VO\PaymentNumber;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PaymentNumberTest extends TestCase
{
    public function test_from_sequence_and_date_matches_template(): void
    {
        $number = PaymentNumber::fromSequenceAndDate(128, new DateTimeImmutable('2026-07-17'));

        $this->assertSame('PMT-26-128', $number->value);
    }

    public function test_rejects_invalid_format(): void
    {
        $this->expectException(DomainException::class);
        new PaymentNumber('PAY-26-1');
    }
}
