<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Command\CreateEquipmentHandler;
use App\Application\Crm\Command\DeleteEquipmentHandler;
use App\Application\Crm\Command\UpdateEquipmentHandler;
use App\Application\Crm\Query\GetEquipmentHandler;
use App\Application\Crm\Query\ListEquipmentHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EquipmentController extends Controller
{
    public function __construct(
        private ListEquipmentHandler $listEquipment,
        private GetEquipmentHandler $getEquipment,
        private CreateEquipmentHandler $createEquipment,
        private UpdateEquipmentHandler $updateEquipment,
        private DeleteEquipmentHandler $deleteEquipment,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $clientId = $request->query('client_id');
        $clientId = $clientId !== null && $clientId !== '' ? (int) $clientId : null;
        $q = $request->query('q');
        $q = is_string($q) && trim($q) !== '' ? trim($q) : null;
        $asMaster = $request->attributes->get('actor_type') === 'masters';

        if ($asMaster) {
            $clientId = null;
        }

        $items = $this->listEquipment->handle($clientId, $q);

        return response()->json([
            'data' => array_map(
                static fn ($item) => $asMaster ? $item->toCatalogArray() : $item->toArray(),
                $items,
            ),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->getEquipment->handle($id);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request, requireClientId: true);

        $item = $this->createEquipment->handle(
            (int) $data['client_id'],
            $data['name'],
            $data['brand'],
            $data['type'],
            $data['modules'] ?? [],
        );

        return response()->json($item->toArray(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $this->validatedPayload($request, requireClientId: false);

        $item = $this->updateEquipment->handle(
            $id,
            $data['name'],
            $data['brand'],
            $data['type'],
            $data['modules'] ?? [],
        );

        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function destroy(int $id): JsonResponse|Response
    {
        if (! $this->deleteEquipment->handle($id)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->noContent();
    }

    /**
     * @return array{
     *     client_id?: int,
     *     name: string,
     *     brand: string,
     *     type: string,
     *     modules?: list<array{name: string, serial_number: string}>
     * }
     */
    private function validatedPayload(Request $request, bool $requireClientId): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'modules' => ['nullable', 'array'],
            'modules.*.name' => ['required_with:modules', 'string', 'max:255'],
            'modules.*.serial_number' => ['required_with:modules', 'string', 'max:255'],
        ];

        if ($requireClientId) {
            $rules['client_id'] = ['required', 'integer', 'min:1'];
        }

        return $request->validate($rules);
    }
}
