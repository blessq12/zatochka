<?php

namespace App\Services\Document\Factories;

use App\Services\Document\Contracts\DocumentGeneratorInterface;
use App\Services\Document\Generators\AcceptanceDocumentGenerator;
use App\Services\Document\Generators\IssuanceDocumentGenerator;
use InvalidArgumentException;

class DocumentGeneratorFactory
{
    public const TYPE_ACCEPTANCE = 'acceptance';
    public const TYPE_ISSUANCE = 'issuance';

    /**
     * Создает генератор документов по типу
     *
     * @param string $type
     * @return DocumentGeneratorInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $type): DocumentGeneratorInterface
    {
        return match ($type) {
            self::TYPE_ACCEPTANCE => new AcceptanceDocumentGenerator(),
            self::TYPE_ISSUANCE => new IssuanceDocumentGenerator(),
            default => throw new InvalidArgumentException("Неизвестный тип документа: {$type}"),
        };
    }

    /**
     * Возвращает список доступных типов документов
     *
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_ACCEPTANCE => 'Акт приема',
            self::TYPE_ISSUANCE => 'Акт выдачи',
        ];
    }
}
