<?php

namespace App\Application\UseCases\Auth;

abstract class BaseAuthUseCase implements AuthUseCaseInterface
{
    protected array $data;

    // Все репозитории и мапперы для авторизации
    protected \App\Domain\Company\Repository\UserRepository $userRepository;
    protected \App\Domain\Company\Mapper\UserMapper $userMapper;

    public function __construct()
    {
        // Подтягиваем зависимости для авторизации
        $this->userRepository = app(\App\Domain\Company\Repository\UserRepository::class);
        $this->userMapper = app(\App\Domain\Company\Mapper\UserMapper::class);
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

    abstract protected function validateSpecificData(): self;
    abstract public function execute(): mixed;
}
