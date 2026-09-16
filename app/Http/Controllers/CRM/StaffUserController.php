<?php

namespace App\Http\Controllers\CRM;

use App\Application\CRM\Command\RegisterManagerCommand;
use App\Application\CRM\Command\RegisterManagerHandler;
use App\Application\CRM\Command\RegisterMasterCommand;
use App\Application\CRM\Command\RegisterMasterHandler;
use App\Application\CRM\Command\UpdateManagerCommand;
use App\Application\CRM\Command\UpdateManagerHandler;
use App\Application\CRM\Command\UpdateMasterCommand;
use App\Application\CRM\Command\UpdateMasterHandler;
use App\Application\Identity\Command\ChangeManagerPasswordCommand;
use App\Application\Identity\Command\ChangeManagerPasswordHandler;
use App\Application\Identity\Command\ChangeMasterPasswordCommand;
use App\Application\Identity\Command\ChangeMasterPasswordHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Domain\CRM\Repository\MasterRepository;
use App\Http\Controllers\Controller;
use App\Infrastructure\CRM\Model\ManagerModel;
use App\Infrastructure\CRM\Model\MasterModel;
use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

final class StaffUserController extends Controller
{
    public function __construct(
        private RegisterManagerHandler $registerManager,
        private RegisterMasterHandler $registerMaster,
        private UpdateManagerHandler $updateManager,
        private UpdateMasterHandler $updateMaster,
        private ChangeManagerPasswordHandler $changeManagerPassword,
        private ChangeMasterPasswordHandler $changeMasterPassword,
        private ManagerRepository $managers,
        private MasterRepository $masters,
        private EntityIdGenerator $ids,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $managers = ManagerModel::query()->orderBy('id')->get()
            ->map(fn (ManagerModel $m): array => $this->serializeStaff($m->id, $m->name, $m->email, 'manager', $m->created_at?->toIso8601String()));

        $masters = MasterModel::query()->orderBy('id')->get()
            ->map(fn (MasterModel $m): array => $this->serializeStaff($m->id, $m->name, $m->email, 'master', $m->created_at?->toIso8601String()));

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $managers->concat($masters)->sortBy('id')->values();

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $items = $items->filter(function (array $row) use ($needle): bool {
                return str_contains(mb_strtolower((string) $row['name']), $needle)
                    || str_contains(mb_strtolower((string) $row['email']), $needle);
            })->values();
        }

        return $this->ok(['items' => $items->all()]);
    }

    public function show(int $userId): JsonResponse
    {
        $staff = $this->findStaff($userId);

        if ($staff === null) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        return $this->ok($staff);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['manager', 'master'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $staffId = $this->ids->next('staff')->value;

        if ($data['role'] === 'manager') {
            $this->registerManager->handle(new RegisterManagerCommand(
                $staffId,
                $data['name'],
                $data['email'],
                $data['password'],
            ));
        } else {
            $this->registerMaster->handle(new RegisterMasterCommand(
                $staffId,
                $data['name'],
                $data['email'],
                $data['password'],
            ));
        }

        return $this->created($this->findStaff($staffId));
    }

    public function update(Request $request, int $userId): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['manager', 'master'])],
        ]);

        $current = $this->findStaff($userId);

        if ($current === null) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        if ($current['role'] !== $data['role']) {
            throw new DomainException('Смена роли сотрудника не поддерживается. Создайте новую учётку.');
        }

        if ($data['role'] === 'manager') {
            $this->updateManager->handle(new UpdateManagerCommand(
                $userId,
                $data['name'],
                $data['email'],
            ));
        } else {
            $this->updateMaster->handle(new UpdateMasterCommand(
                $userId,
                $data['name'],
                $data['email'],
            ));
        }

        return $this->ok($this->findStaff($userId));
    }

    public function changePassword(Request $request, int $userId): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $current = $this->findStaff($userId);

        if ($current === null) {
            return response()->json(['message' => 'Сотрудник не найден.'], 404);
        }

        if ($current['role'] === 'manager') {
            $this->changeManagerPassword->handle(new ChangeManagerPasswordCommand($userId, $data['password']));
        } else {
            $this->changeMasterPassword->handle(new ChangeMasterPasswordCommand($userId, $data['password']));
        }

        return $this->ok($this->findStaff($userId));
    }

    public function destroy(int $userId): JsonResponse
    {
        $id = new EntityId($userId);

        if ($this->managers->findById($id) !== null) {
            ManagerAccountModel::query()->find($userId)?->tokens()->delete();
            $this->managers->delete($id);

            return $this->noContent();
        }

        if ($this->masters->findById($id) !== null) {
            MasterAccountModel::query()->find($userId)?->tokens()->delete();
            $this->masters->delete($id);

            return $this->noContent();
        }

        return response()->json(['message' => 'Сотрудник не найден.'], 404);
    }

    /** @return array<string, mixed>|null */
    private function findStaff(int $id): ?array
    {
        $manager = ManagerModel::query()->find($id);
        if ($manager !== null) {
            return $this->serializeStaff(
                $manager->id,
                $manager->name,
                $manager->email,
                'manager',
                $manager->created_at?->toIso8601String(),
            );
        }

        $master = MasterModel::query()->find($id);
        if ($master !== null) {
            return $this->serializeStaff(
                $master->id,
                $master->name,
                $master->email,
                'master',
                $master->created_at?->toIso8601String(),
            );
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function serializeStaff(int $id, string $name, string $email, string $role, ?string $createdAt): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'created_at' => $createdAt,
        ];
    }
}
