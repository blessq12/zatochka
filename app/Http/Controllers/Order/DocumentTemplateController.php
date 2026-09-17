<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\PreviewDocumentTemplateHandler;
use App\Application\Order\Command\UpdateDocumentTemplateHandler;
use App\Application\Order\Query\ListDocumentTemplatesHandler;
use App\Application\Order\Support\ActorDisplayNameResolver;
use App\Domain\Order\Enum\DocumentType;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class DocumentTemplateController extends Controller
{
    public function __construct(
        private ListDocumentTemplatesHandler $listTemplates,
        private UpdateDocumentTemplateHandler $updateTemplate,
        private PreviewDocumentTemplateHandler $previewTemplate,
        private ActorDisplayNameResolver $displayNames,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->listTemplates->handle());
    }

    public function update(Request $request, string $type): JsonResponse
    {
        $documentType = $this->parseType($type);
        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $template = $this->updateTemplate->handle(
            $documentType,
            $data['body'],
            $request->user()?->id !== null ? (int) $request->user()->id : null,
        );

        return response()->json([
            'type' => $template->type()->value,
            'label' => $template->type()->label(),
            'body' => $template->body(),
            'updated_at' => $template->updatedAt()?->format(DATE_ATOM),
        ]);
    }

    public function preview(Request $request, string $type): Response
    {
        $documentType = $this->parseType($type);
        $data = $request->validate([
            'body' => ['required', 'string'],
            'order_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $managerName = $this->displayNames->resolve(
            (string) $request->attributes->get('actor_type'),
            (int) $request->attributes->get('actor_id'),
        );

        $document = $this->previewTemplate->handle(
            $documentType,
            $data['body'],
            isset($data['order_id']) ? (int) $data['order_id'] : null,
            $managerName,
        );

        return response($document->content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->filename.'"',
        ]);
    }

    private function parseType(string $type): DocumentType
    {
        try {
            return DocumentType::from($type);
        } catch (\ValueError) {
            throw new DomainException('Unknown document type.');
        }
    }
}
