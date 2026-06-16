<?php

namespace App\Domain\Equipment\Entity;

final class Equipment
{
    /**
     * @param  list<string>  $serialNumbers
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $brand,
        private ?string $model,
        private array $serialNumbers,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function brand(): ?string
    {
        return $this->brand;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    /** @return list<string> */
    public function serialNumbers(): array
    {
        return $this->serialNumbers;
    }

    /**
     * @param  list<string>  $serialNumbers
     */
    public static function register(
        string $name,
        array $serialNumbers = [],
        ?string $brand = null,
        ?string $model = null,
    ): self {
        return new self(
            id: null,
            name: $name,
            brand: $brand,
            model: $model,
            serialNumbers: $serialNumbers,
        );
    }
}
