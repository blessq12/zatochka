<?php

namespace App\Http\Controllers\Equipment;

use App\Application\Equipment\Command\AddComponentCommand;
use App\Application\Equipment\Command\AddComponentHandler;
use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Equipment\Command\RegisterSerialNumberCommand;
use App\Application\Equipment\Command\RegisterSerialNumberHandler;
use App\Application\Equipment\Query\GetEquipmentByIdHandler;
use App\Application\Equipment\Query\GetEquipmentByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EquipmentController extends Controller
{
    public function __construct(
        private RegisterEquipmentHandler $registerEquipment,
        private AddComponentHandler $addComponent,
        private RegisterSerialNumberHandler $registerSerialNumber,
        private GetEquipmentByIdHandler $getEquipmentById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clientId' => ['required', 'integer'],
            'title' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $equipmentId = $this->ids->next('equipment')->value;

        $this->registerEquipment->handle(new RegisterEquipmentCommand(
            $equipmentId,
            (int) $data['clientId'],
            $data['title'],
            $data['notes'] ?? null,
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
        ]);

        $componentId = $this->ids->next('equipment_component')->value;

        $this->addComponent->handle(new AddComponentCommand(
            $equipmentId,
            $componentId,
            $data['name'],
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
