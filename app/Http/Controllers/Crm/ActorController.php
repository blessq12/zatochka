<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Command\DeleteActorHandler;
use App\Application\Crm\Command\UpdateActorHandler;
use App\Application\Crm\Query\GetActorHandler;
use App\Domain\Crm\ActorType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ActorController extends Controller
{
    public function __construct(
        private GetActorHandler $getActor,
        private UpdateActorHandler $updateActor,
        private DeleteActorHandler $deleteActor,
    ) {}

    public function show(string $type, int $id): JsonResponse
    {
        $actorType = ActorType::fromRoute($type);
        $actor = $this->getActor->handle($actorType, $id);

        if ($actor === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($actor->toArray());
    }

    public function update(string $type, int $id): JsonResponse
    {
        $actorType = ActorType::fromRoute($type);
        $actor = $this->updateActor->handle($actorType, $id);

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
}
