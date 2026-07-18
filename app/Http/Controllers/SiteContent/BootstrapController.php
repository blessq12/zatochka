<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Query\GetSiteBootstrapHandler;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;

final class BootstrapController extends Controller
{
    public function __construct(
        private GetSiteBootstrapHandler $getBootstrap,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            return $this->ok($this->getBootstrap->handle());
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
