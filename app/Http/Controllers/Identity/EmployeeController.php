<?php

namespace App\Http\Controllers\Identity;

use App\Application\Identity\Command\AssignRoleCommand;
use App\Application\Identity\Command\AssignRoleHandler;
use App\Application\Identity\Command\HireEmployeeCommand;
use App\Application\Identity\Command\HireEmployeeHandler;
use App\Application\Identity\Query\GetEmployeeByIdHandler;
use App\Application\Identity\Query\GetEmployeeByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeController extends Controller
{
    public function __construct(
        private HireEmployeeHandler $hireEmployee,
        private AssignRoleHandler $assignRole,
        private GetEmployeeByIdHandler $getEmployeeById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $employeeId = $this->ids->next('employee')->value;

        $this->hireEmployee->handle(new HireEmployeeCommand(
            $employeeId,
            $data['name'],
            $data['email'],
        ));

        return $this->created($this->getEmployeeById->handle(new GetEmployeeByIdQuery($employeeId)));
    }

    public function show(int $employeeId): JsonResponse
    {
        $employee = $this->getEmployeeById->handle(new GetEmployeeByIdQuery($employeeId));

        if ($employee === null) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        return $this->ok($employee);
    }

    public function assignRole(Request $request, int $employeeId): JsonResponse
    {
        $data = $request->validate([
            'roleId' => ['required', 'integer'],
        ]);

        $this->assignRole->handle(new AssignRoleCommand($employeeId, (int) $data['roleId']));

        return $this->ok($this->getEmployeeById->handle(new GetEmployeeByIdQuery($employeeId)));
    }
}
