<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Query\GetLegalDocumentHandler;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;

final class LegalDocumentController extends Controller
{
    public function __construct(
        private GetLegalDocumentHandler $getLegalDocument,
    ) {}

    public function __invoke(string $type): JsonResponse
    {
        try {
            return $this->ok($this->getLegalDocument->handle($type)->toArray());
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
