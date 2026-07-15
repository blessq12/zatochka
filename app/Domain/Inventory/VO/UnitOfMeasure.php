<?php

namespace App\Domain\Inventory\VO;

enum UnitOfMeasure: string
{
    case Piece = 'piece';
    case Pack = 'pack';
    case Set = 'set';
    case Pair = 'pair';
    case Kilogram = 'kg';
    case Gram = 'g';
    case Liter = 'l';
    case Milliliter = 'ml';
    case Meter = 'm';
    case Centimeter = 'cm';
    case Roll = 'roll';
    case Bottle = 'bottle';
    case Can = 'can';
}
