<?php

namespace App\Application\OrderFulfillment\Port;

use App\Application\OrderFulfillment\ReadModel\OrderDocumentData;

interface DocumentTemplateRendererInterface
{
    public function render(string $body, OrderDocumentData $data, string $documentTitle): string;
}
