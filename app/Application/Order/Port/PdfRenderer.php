<?php

namespace App\Application\Order\Port;

interface PdfRenderer
{
    public function render(string $html): string;
}
