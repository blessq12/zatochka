<?php

namespace App\Services\Document\Generators;

use App\Models\Order;
use App\Services\Document\Contracts\DocumentGeneratorInterface;
use App\Services\Document\DTOs\DocumentData;
use Barryvdh\DomPDF\Facade\Pdf;

class AcceptanceDocumentGenerator implements DocumentGeneratorInterface
{
    public function generate(Order $order): string
    {
        $data = DocumentData::fromOrder($order);

        $pdf = Pdf::loadView('documents.acceptance', [
            'data' => $data,
            'order' => $order,
            'documentTitle' => $this->getDocumentName(),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'dejavusans');
        $pdf->setOption('defaultEncoding', 'UTF-8');

        $fileName = $this->getFileName($order);
        $filePath = storage_path('app/temp/' . $fileName);

        // Создаем директорию если её нет
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        file_put_contents($filePath, $pdf->output());

        return $filePath;
    }

    public function getDocumentName(): string
    {
        return 'Акт приема инструмента/оборудования';
    }

    public function getFileName(Order $order): string
    {
        return sprintf(
            'acceptance_%s_%s.pdf',
            $order->order_number,
            now()->format('YmdHis')
        );
    }
}
