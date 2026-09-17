<?php

namespace App\Application\Order\Port;

use App\Application\Order\ReadModel\OrderDocumentData;

interface DocumentTemplateRendererInterface
{
    public function render(string $body, OrderDocumentData $data, string $documentTitle): string;
}
