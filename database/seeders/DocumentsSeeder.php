<?php

namespace Database\Seeders;

use App\Domain\Documents\VO\DocumentType;
use App\Domain\Documents\VO\PdfTemplateKind;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (DocumentType::cases() as $type) {
            DB::table('legal_documents')->updateOrInsert(
                ['type' => $type->value],
                [
                    'title' => $type->label(),
                    'body_html' => $this->loadTemplate($type->value.'.html'),
                    'updated_at' => $now,
                ],
            );
        }

        $templates = [
            [
                'id' => 1,
                'kind' => PdfTemplateKind::ReceptionReceipt->value,
                'name' => PdfTemplateKind::ReceptionReceipt->label(),
                'body_html' => $this->loadTemplate('reception_receipt.html'),
            ],
            [
                'id' => 2,
                'kind' => PdfTemplateKind::IssueAct->value,
                'name' => PdfTemplateKind::IssueAct->label(),
                'body_html' => $this->loadTemplate('issue_act.html'),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('document_templates')->updateOrInsert(
                ['id' => $template['id']],
                [
                    'kind' => $template['kind'],
                    'name' => $template['name'],
                    'body_html' => $template['body_html'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    private function loadTemplate(string $filename): string
    {
        $path = database_path('seeders/data/'.$filename);

        if (! is_file($path)) {
            throw new RuntimeException(sprintf('Document template file not found: %s', $path));
        }

        $html = file_get_contents($path);

        if ($html === false || trim($html) === '') {
            throw new RuntimeException(sprintf('Document template file is empty: %s', $path));
        }

        return $html;
    }
}
