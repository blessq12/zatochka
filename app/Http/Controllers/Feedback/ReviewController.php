<?php

namespace App\Http\Controllers\Feedback;

use App\Application\Feedback\Command\DeleteReviewCommand;
use App\Application\Feedback\Command\DeleteReviewHandler;
use App\Application\Feedback\Command\HideReviewCommand;
use App\Application\Feedback\Command\HideReviewHandler;
use App\Application\Feedback\Command\PublishReviewCommand;
use App\Application\Feedback\Command\PublishReviewHandler;
use App\Application\Feedback\Command\RejectReviewCommand;
use App\Application\Feedback\Command\RejectReviewHandler;
use App\Application\Feedback\Command\RestoreReviewCommand;
use App\Application\Feedback\Command\RestoreReviewHandler;
use App\Application\Feedback\Command\SetReviewManagerReplyCommand;
use App\Application\Feedback\Command\SetReviewManagerReplyHandler;
use App\Application\Feedback\DTO\ReviewDTO;
use App\Application\Feedback\ReadPort\ReviewReadPort;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReviewController extends Controller
{
    public function __construct(
        private ReviewReadPort $reviews,
        private PublishReviewHandler $publishReview,
        private RejectReviewHandler $rejectReview,
        private HideReviewHandler $hideReview,
        private RestoreReviewHandler $restoreReview,
        private DeleteReviewHandler $deleteReview,
        private SetReviewManagerReplyHandler $setManagerReply,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', 'pending');

        $items = match ($status) {
            'published' => $this->reviews->listPublished(),
            default => $this->reviews->listPending(),
        };

        return $this->ok([
            'items' => array_map(fn (ReviewDTO $r): array => $this->mapReview($r), $items),
        ]);
    }

    public function show(int $reviewId): JsonResponse
    {
        $review = $this->reviews->findById($reviewId);

        if ($review === null) {
            return response()->json(['message' => 'Отзыв не найден.'], 404);
        }

        return $this->ok($this->mapReview($review));
    }

    public function publish(Request $request, int $reviewId): JsonResponse
    {
        $this->publishReview->handle(new PublishReviewCommand(
            $reviewId,
            (int) $request->user()->id,
        ));

        return $this->ok($this->mapReview($this->reviews->findById($reviewId)));
    }

    public function reject(Request $request, int $reviewId): JsonResponse
    {
        $this->rejectReview->handle(new RejectReviewCommand(
            $reviewId,
            (int) $request->user()->id,
        ));

        return $this->ok($this->mapReview($this->reviews->findById($reviewId)));
    }

    public function hide(Request $request, int $reviewId): JsonResponse
    {
        $this->hideReview->handle(new HideReviewCommand($reviewId));

        return $this->ok($this->mapReview($this->reviews->findById($reviewId)));
    }

    public function restore(Request $request, int $reviewId): JsonResponse
    {
        $this->restoreReview->handle(new RestoreReviewCommand($reviewId));

        return $this->ok($this->mapReview($this->reviews->findById($reviewId)));
    }

    public function destroy(Request $request, int $reviewId): JsonResponse
    {
        $this->deleteReview->handle(new DeleteReviewCommand($reviewId));

        return $this->noContent();
    }

    public function reply(Request $request, int $reviewId): JsonResponse
    {
        $data = $request->validate([
            'reply' => ['required', 'string'],
        ]);

        $this->setManagerReply->handle(new SetReviewManagerReplyCommand(
            $reviewId,
            $data['reply'],
        ));

        return $this->ok($this->mapReview($this->reviews->findById($reviewId)));
    }

    /** @return array<string, mixed> */
    private function mapReview(?ReviewDTO $review): array
    {
        if ($review === null) {
            return [];
        }

        return [
            'id' => $review->id,
            'orderId' => $review->orderId,
            'clientId' => $review->clientId,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'managerReply' => $review->managerReply,
            'status' => $review->status,
            'moderatedBy' => $review->moderatedBy,
            'submittedAt' => $review->submittedAt,
            'moderatedAt' => $review->moderatedAt,
            'hiddenAt' => $review->hiddenAt,
            'deletedAt' => $review->deletedAt,
        ];
    }
}
