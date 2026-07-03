<?php

namespace App\Http\Controllers\Cp;

use App\Application\OrderFulfillment\Command\GenerateDocumentCommand;
use App\Application\OrderFulfillment\CommandHandler\GenerateDocumentHandler;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class OrderDocumentController
{
    public function __invoke(
        int $orderId,
        string $type,
        Request $request,
        GenerateDocumentHandler $handler,
    ): Response {
        /** @var UserModel $user */
        $user = $request->user();

        $document = $handler->handle(new GenerateDocumentCommand(
            orderId: $orderId,
            type: DocumentType::from($type),
            managerName: trim($user->name.' '.$user->surname),
            userId: $user->id,
        ));

        return response($document->content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->filename.'"',
        ]);
    }
}
