<?php

namespace Tests\Unit\Domain\Equipment;

use App\Domain\Equipment\VO\EquipmentNumber;
use App\Shared\Domain\DomainException;
use PHPUnit\Framework\TestCase;

final class EquipmentNumberTest extends TestCase
{
    public function test_from_sequence_matches_template(): void
    {
        $number = EquipmentNumber::fromSequence(128);

        $this->assertSame('EQP-128', $number->value);
    }

    public function test_rejects_invalid_format(): void
    {
        $this->expectException(DomainException::class);
        new EquipmentNumber('EQ-128');
    }
}
