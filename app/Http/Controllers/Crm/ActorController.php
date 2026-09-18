<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Command\CreateWalkInClientHandler;
use App\Application\Crm\Command\DeleteActorHandler;
use App\Application\Crm\Command\UpdateActorHandler;
use App\Application\Crm\Query\GetActorHandler;
use App\Application\Crm\Query\ListActorsHandler;
use App\Domain\Crm\ActorType;
use App\Http\Controllers\Controller;
use App\Shared\Domain\ForbiddenException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ActorController extends Controller
{
    public function __construct(
        private ListActorsHandler $listActors,
        private GetActorHandler $getActor,
        private UpdateActorHandler $updateActor,
        private DeleteActorHandler $deleteActor,
        private CreateWalkInClientHandler $createWalkInClient,
    ) {}

    public function index(Request $request, string $type): JsonResponse
    {
        $actorType = ActorType::fromRoute($type);
        $query = $request->query('q');
        $query = is_string($query) ? $query : null;
        $recent = filter_var($request->query('recent'), FILTER_VALIDATE_BOOLEAN);

        $actors = $this->listActors->handle($actorType, $query, $recent);

        return response()->json([
            'data' => array_map(
                static fn ($actor) => $actor->toArray(),
                $actors,
            ),
        ]);
    }

    public function storeWalkInClient(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $actor = $this->createWalkInClient->handle($data['name'], $data['phone']);

        return response()->json($actor->toArray(), 201);
    }

    public function show(string $type, int $id): JsonResponse
    {
        $actorType = ActorType::fromRoute($type);
        $actor = $this->getActor->handle($actorType, $id);

        if ($actor === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($actor->toArray());
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $this->assertCanUpdateActor($request, $type, $id);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'birthday' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $attributes = [];
        foreach (['name', 'phone', 'birthday', 'delivery_address'] as $field) {
            if ($request->exists($field)) {
                $attributes[$field] = $data[$field] ?? null;
            }
        }

        $actorType = ActorType::fromRoute($type);
        $actor = $this->updateActor->handle($actorType, $id, $attributes);

        if ($actor === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($actor->toArray());
    }

    public function destroy(string $type, int $id): JsonResponse|Response
    {
        $actorType = ActorType::fromRoute($type);
        $deleted = $this->deleteActor->handle($actorType, $id);

        if (! $deleted) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->noContent();
    }

    private function assertCanUpdateActor(Request $request, string $type, int $id): void
    {
        $callerType = (string) $request->attributes->get('actor_type');
        $callerId = (int) $request->attributes->get('actor_id');

        if ($callerType === ActorType::Manager->value) {
            return;
        }

        if ($callerType !== $type || $callerId !== $id) {
            throw new ForbiddenException('Forbidden for this actor.');
        }
    }
}
