<?php

namespace App\Application\UseCases\Communication;

use App\Application\UseCases\UseCaseInterface;

abstract class BaseCommunicationUseCase implements UseCaseInterface
{

    protected $authContext;
    protected array $data = [];

    public function __construct()
    {
        $this->authContext = auth('sanctum')->user();
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract public function execute(): mixed;
    abstract protected function validateSpecificData(): void;
}
