<?php

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\VO\PermissionCode;
use App\Shared\ValueObject\EntityId;

final readonly class Permission
{
    public function __construct(
        public EntityId $id,
        public PermissionCode $code,
        public string $description,
    ) {}
}
