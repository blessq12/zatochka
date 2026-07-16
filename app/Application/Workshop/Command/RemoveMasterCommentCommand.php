<?php

namespace App\Application\Workshop\Command;

final readonly class RemoveMasterCommentCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $commentId,
        public int $masterId,
    ) {}
}
