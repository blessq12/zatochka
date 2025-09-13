<?php

namespace App\Application\UseCases\Review;

interface ReviewUseCaseInterface
{
    public function loadData(array $data): ReviewUseCaseInterface;

    public function validate(): ReviewUseCaseInterface;

    public function execute(): mixed;
}
