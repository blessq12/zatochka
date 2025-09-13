<?php

namespace App\Application\UseCases\Review;

use App\Domain\Review\Repository\ReviewRepository;

abstract class BaseReviewUseCase implements ReviewUseCaseInterface
{
    protected array $data;
    protected ReviewRepository $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = app(ReviewRepository::class);
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
