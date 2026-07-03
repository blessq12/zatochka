<?php

namespace App\Http\Controllers\Cp;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class DocumentTemplatePreviewController
{
    public function __invoke(string $token): Response
    {
        $content = Cache::pull('document_template_preview_'.$token);

        abort_if(! is_string($content) || $content === '', 404);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
        ]);
    }
}
