<?php

namespace Tests\Unit\Domain\Equipment;

use App\Domain\Equipment\VO\EquipmentType;
use PHPUnit\Framework\TestCase;

final class EquipmentTypeTest extends TestCase
{
    public function test_options_and_labels_are_russian_sot(): void
    {
        $options = EquipmentType::options();

        $this->assertSame('Машинка для стрижки', $options[EquipmentType::Clipper->value]);
        $this->assertSame('Триммер', $options[EquipmentType::Trimmer->value]);
        $this->assertSame('Бритва', $options[EquipmentType::Shaver->value]);
        $this->assertSame('Фен', $options[EquipmentType::Dryer->value]);
        $this->assertSame('Другое', $options[EquipmentType::Other->value]);
        $this->assertCount(count(EquipmentType::cases()), $options);
        $this->assertSame(EquipmentType::values(), array_keys($options));
    }

    public function test_try_label(): void
    {
        $this->assertSame('Машинка для стрижки', EquipmentType::tryLabel('clipper'));
        $this->assertNull(EquipmentType::tryLabel('unknown'));
        $this->assertNull(EquipmentType::tryLabel(null));
    }
}
