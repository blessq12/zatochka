<?php

namespace App\Http\Controllers\CRM;

use App\Application\CRM\Command\AddComponentCommand;
use App\Application\CRM\Command\AddComponentHandler;
use App\Application\CRM\Command\RegisterEquipmentCommand;
use App\Application\CRM\Command\RegisterEquipmentHandler;
use App\Application\CRM\Command\RegisterSerialNumberCommand;
use App\Application\CRM\Command\RegisterSerialNumberHandler;
use App\Application\CRM\DTO\EquipmentPartDTO;
use App\Application\CRM\Query\GetEquipmentByIdHandler;
use App\Application\CRM\Query\GetEquipmentByIdQuery;
use App\Application\CRM\ReadPort\EquipmentOrderHistoryPort;
use App\Application\CRM\ReadPort\EquipmentReadPort;
use App\Domain\CRM\VO\EquipmentType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EquipmentController extends Controller
{
    public function __construct(
        private RegisterEquipmentHandler $registerEquipment,
        private AddComponentHandler $addComponent,
        private RegisterSerialNumberHandler $registerSerialNumber,
        private GetEquipmentByIdHandler $getEquipmentById,
        private EquipmentReadPort $equipmentRead,
        private EquipmentOrderHistoryPort $orderHistory,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $this->equipmentRead->search(
            $data['query'] ?? null,
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 20),
        );

        return response()->json([
            'data' => $this->serialize($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function orderHistory(int $equipmentId): JsonResponse
    {
        return $this->ok($this->orderHistory->historyForEquipment($equipmentId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clientId' => ['nullable', 'integer'],
            'title' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'modelName' => ['required', 'string'],
            'equipmentType' => ['required', 'string', Rule::enum(EquipmentType::class)],
            'parts' => ['nullable', 'array'],
            'parts.*.name' => ['required', 'string'],
            'parts.*.serialNumber' => ['nullable', 'string'],
        ]);

        $equipmentId = $this->ids->next('equipment')->value;

        $parts = [];

        foreach ($data['parts'] ?? [] as $part) {
            $parts[] = new EquipmentPartDTO(
                $this->ids->next('equipment_component')->value,
                $part['name'],
                $part['serialNumber'] ?? null,
            );
        }

        $this->registerEquipment->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $data['title'],
            $data['brand'],
            $data['modelName'],
            $data['equipmentType'],
            isset($data['clientId']) ? (int) $data['clientId'] : null,
            $parts,
        ));

        return $this->created($this->getEquipmentById->handle(new GetEquipmentByIdQuery($equipmentId)));
    }

    public function show(int $equipmentId): JsonResponse
    {
        $equipment = $this->getEquipmentById->handle(new GetEquipmentByIdQuery($equipmentId));

        if ($equipment === null) {
            return response()->json(['message' => 'Equipment not found.'], 404);
        }

        return $this->ok($equipment);
    }

    public function addComponent(Request $request, int $equipmentId): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'serialNumber' => ['nullable', 'string'],
        ]);

        $componentId = $this->ids->next('equipment_component')->value;

        $this->addComponent->handle(new AddComponentCommand(
            $equipmentId,
            $componentId,
            $data['name'],
            $data['serialNumber'] ?? null,
        ));

        return $this->ok($this->getEquipmentById->handle(new GetEquipmentByIdQuery($equipmentId)));
    }

    public function registerSerial(Request $request, int $equipmentId, int $componentId): JsonResponse
    {
        $data = $request->validate([
            'serialNumber' => ['required', 'string'],
        ]);

        $this->registerSerialNumber->handle(new RegisterSerialNumberCommand(
            $equipmentId,
            $componentId,
            $data['serialNumber'],
        ));

        return $this->ok($this->getEquipmentById->handle(new GetEquipmentByIdQuery($equipmentId)));
    }
}
