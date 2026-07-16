<?php

namespace App\Application\Workshop\Command;

final readonly class AddMasterCommentCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $commentId,
        public int $masterId,
        public string $text,
        public ?int $orderItemId = null,
    ) {}
}
