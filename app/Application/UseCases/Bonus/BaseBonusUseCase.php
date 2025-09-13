<?php

namespace App\Application\UseCases\Bonus;

use App\Domain\Bonus\Repository\BonusAccountRepository;
use App\Domain\Bonus\Repository\BonusTransactionRepository;

abstract class BaseBonusUseCase implements BonusUseCaseInterface
{
    protected array $data;
    protected BonusAccountRepository $bonusAccountRepository;
    protected BonusTransactionRepository $bonusTransactionRepository;

    public function __construct()
    {
        $this->bonusAccountRepository = app(BonusAccountRepository::class);
        $this->bonusTransactionRepository = app(BonusTransactionRepository::class);
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

    abstract public function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
