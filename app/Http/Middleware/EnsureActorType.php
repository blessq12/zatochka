<?php

namespace App\Http\Middleware;

use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Infrastructure\Identity\Eloquent\IdentityModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActorType
{
    public function __construct(
        private IdentityActorLinkRepository $links,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$actorTypes): Response
    {
        /** @var IdentityModel|null $identity */
        $identity = $request->user();

        if ($identity === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $link = $this->links->findByIdentityId((int) $identity->id);

        if ($link === null) {
            return response()->json(['message' => 'Identity is not linked to an actor.'], 403);
        }

        if ($actorTypes !== [] && ! in_array($link->actorType, $actorTypes, true)) {
            return response()->json(['message' => 'Forbidden for this actor type.'], 403);
        }

        $request->attributes->set('actor_type', $link->actorType);
        $request->attributes->set('actor_id', $link->actorId);

        return $next($request);
    }
}
