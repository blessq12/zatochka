<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Command\UpdateLegalDocumentCommand;
use App\Application\SiteContent\Command\UpdateLegalDocumentHandler;
use App\Application\SiteContent\Query\GetLegalDocumentHandler;
use App\Domain\SiteContent\VO\DocumentType;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ManageLegalDocumentController extends Controller
{
    public function __construct(
        private GetLegalDocumentHandler $getLegalDocument,
        private UpdateLegalDocumentHandler $updateLegalDocument,
    ) {}

    public function index(): JsonResponse
    {
        $items = [];
        foreach (DocumentType::cases() as $type) {
            try {
                $items[] = $this->getLegalDocument->handle($type->publicSlug())->toArray();
            } catch (DomainException) {
                $items[] = [
                    'type' => $type->value,
                    'slug' => $type->publicSlug(),
                    'title' => $type->label(),
                    'body_html' => '',
                    'updated_at' => null,
                ];
            }
        }

        return $this->ok(['items' => $items]);
    }

    public function show(string $slug): JsonResponse
    {
        try {
            return $this->ok($this->getLegalDocument->handle($slug)->toArray());
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'body_html' => ['required', 'string'],
        ]);

        $documentType = DocumentType::fromPublicSlug($slug);
        if ($documentType === null) {
            return response()->json(['message' => 'Документ не найден.'], 404);
        }

        $this->updateLegalDocument->handle(new UpdateLegalDocumentCommand(
            $documentType,
            $data['title'],
            $data['body_html'],
        ));

        return $this->ok($this->getLegalDocument->handle($slug)->toArray());
    }
}
