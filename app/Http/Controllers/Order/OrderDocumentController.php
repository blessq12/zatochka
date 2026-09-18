<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\GenerateDocumentHandler;
use App\Application\Order\Support\ActorDisplayNameResolver;
use App\Domain\Order\Enum\DocumentType;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class OrderDocumentController extends Controller
{
    public function __construct(
        private GenerateDocumentHandler $generateDocument,
        private ActorDisplayNameResolver $displayNames,
    ) {}

    public function __invoke(Request $request, int $id, string $type): Response
    {
        try {
            $documentType = DocumentType::from($type);
        } catch (\ValueError) {
            throw new DomainException('Unknown document type.');
        }

        $managerName = $this->displayNames->resolve(
            (string) $request->attributes->get('actor_type'),
            (int) $request->attributes->get('actor_id'),
        );

        $document = $this->generateDocument->handle(
            $id,
            $documentType,
            $managerName,
        );

        return response($document->content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->filename.'"',
        ]);
    }
}
