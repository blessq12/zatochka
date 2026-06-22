<?php

namespace Tests\Unit\Domain\ClientPortal;

use App\Domain\ClientPortal\Entity\Review;
use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;
use PHPUnit\Framework\TestCase;

final class ReviewTest extends TestCase
{
    public function test_опубликовать_можно_только_отзыв_на_модерации(): void
    {
        $review = Review::submit(1, 2, 5, null);

        $approved = $review->approve();

        $this->assertSame(ReviewStatus::Approved, $approved->status());
    }

    public function test_нельзя_повторно_опубликовать_отзыв(): void
    {
        $review = Review::submit(1, 2, 5, null)->approve();

        $this->expectException(ReviewPolicyViolation::class);

        $review->approve();
    }

    public function test_нельзя_повторно_отклонить_отзыв(): void
    {
        $review = Review::submit(1, 2, 5, null)->reject();

        $this->expectException(ReviewPolicyViolation::class);

        $review->reject();
    }
}
