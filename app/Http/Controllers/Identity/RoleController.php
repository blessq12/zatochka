<?php

namespace App\Http\Controllers\Identity;

use App\Application\Identity\Command\CreateRoleCommand;
use App\Application\Identity\Command\CreateRoleHandler;
use App\Application\Identity\Command\GrantPermissionCommand;
use App\Application\Identity\Command\GrantPermissionHandler;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoleController extends Controller
{
    public function __construct(
        private CreateRoleHandler $createRole,
        private GrantPermissionHandler $grantPermission,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $roleId = $this->ids->next('role')->value;
        $this->createRole->handle(new CreateRoleCommand($roleId, $data['name']));

        return $this->created(['id' => $roleId, 'name' => $data['name']]);
    }

    public function grantPermission(Request $request, int $roleId): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'description' => ['required', 'string'],
        ]);

        $permissionId = $this->ids->next('permission')->value;

        $this->grantPermission->handle(new GrantPermissionCommand(
            $roleId,
            $permissionId,
            $data['code'],
            $data['description'],
        ));

        return $this->ok([
            'roleId' => $roleId,
            'permissionId' => $permissionId,
            'code' => $data['code'],
        ]);
    }
}
