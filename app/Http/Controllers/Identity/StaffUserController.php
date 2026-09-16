<?php

namespace App\Http\Controllers\Identity;

use App\Application\Identity\Command\ChangeStaffPasswordCommand;
use App\Application\Identity\Command\ChangeStaffPasswordHandler;
use App\Application\Identity\Command\RegisterStaffUserCommand;
use App\Application\Identity\Command\RegisterStaffUserHandler;
use App\Application\Identity\Command\UpdateStaffUserCommand;
use App\Application\Identity\Command\UpdateStaffUserHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class StaffUserController extends Controller
{
    public function __construct(
        private RegisterStaffUserHandler $registerStaffUser,
        private UpdateStaffUserHandler $updateStaffUser,
        private ChangeStaffPasswordHandler $changeStaffPassword,
        private EntityIdGenerator $ids,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = User::query()
            ->whereIn('role', [UserRole::Manager->value, UserRole::Master->value])
            ->orderBy('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $items = $query->get()->map(fn (User $user): array => $this->serializeUser($user));

        return $this->ok(['items' => $items]);
    }

    public function show(int $userId): JsonResponse
    {
        $user = User::query()
            ->whereIn('role', [UserRole::Manager->value, UserRole::Master->value])
            ->find($userId);

        if ($user === null) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        return $this->ok($this->serializeUser($user));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $userId = $this->ids->next('user')->value;

        $this->registerStaffUser->handle(new RegisterStaffUserCommand(
            $userId,
            $data['name'],
            $data['email'],
            $data['role'],
            $data['password'],
        ));

        return $this->created($this->serializeUser(User::query()->findOrFail($userId)));
    }

    public function update(Request $request, int $userId): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::enum(UserRole::class)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $this->updateStaffUser->handle(new UpdateStaffUserCommand(
            $userId,
            $data['name'],
            $data['email'],
            $data['role'],
            $data['password'] ?? null,
        ));

        return $this->ok($this->serializeUser(User::query()->findOrFail($userId)));
    }

    public function changePassword(Request $request, int $userId): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $this->changeStaffPassword->handle(new ChangeStaffPasswordCommand(
            $userId,
            $data['password'],
        ));

        return $this->ok($this->serializeUser(User::query()->findOrFail($userId)));
    }

    public function destroy(int $userId): JsonResponse
    {
        $user = User::query()
            ->whereIn('role', [UserRole::Manager->value, UserRole::Master->value])
            ->find($userId);

        if ($user === null) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        $user->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
