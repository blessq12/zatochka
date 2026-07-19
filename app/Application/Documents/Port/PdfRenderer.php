<?php

namespace App\Application\Documents\Port;

interface PdfRenderer
{
    public function render(string $html): string;
}
