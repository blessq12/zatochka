<?php

namespace App\Application\Feedback\DTO;

final readonly class PublicReviewDTO
{
    public function __construct(
        public int $id,
        public int $rating,
        public ?string $comment,
        public ?string $managerReply,
        public string $clientName,
        public string $submittedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'manager_reply' => $this->managerReply,
            'client_name' => $this->clientName,
            'submitted_at' => $this->submittedAt,
        ];
    }
}
