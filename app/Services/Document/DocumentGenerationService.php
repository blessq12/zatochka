<?php

namespace App\Services\Document;

use App\Models\Order;
use App\Services\Document\Factories\DocumentGeneratorFactory;
use Illuminate\Support\Facades\Storage;

class DocumentGenerationService
{
    /**
     * Генерирует документ для заказа и сохраняет его в MediaLibrary
     *
     * @param Order $order
     * @param string $documentType Тип документа (acceptance или issuance)
     * @param bool $saveToMediaLibrary Сохранять ли в MediaLibrary
     * @return array Массив с путем к файлу и информацией о документе
     */
    public function generateDocument(
        Order $order,
        string $documentType,
        bool $saveToMediaLibrary = true
    ): array {
        $generator = DocumentGeneratorFactory::create($documentType);
        $filePath = $generator->generate($order);
        $fileName = $generator->getFileName($order);

        $result = [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'document_name' => $generator->getDocumentName(),
            'type' => $documentType,
        ];

        if ($saveToMediaLibrary) {
            $media = $order->addMedia($filePath)
                ->usingName($generator->getDocumentName())
                ->usingFileName($fileName)
                ->toMediaCollection('documents');

            $result['media_id'] = $media->id;
            $result['media_url'] = $media->getUrl();
        }

        // Удаляем временный файл после сохранения в MediaLibrary
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return $result;
    }

    /**
     * Генерирует документ и возвращает его как response для скачивания
     *
     * @param Order $order
     * @param string $documentType
     * @return \Illuminate\Http\Response
     */
    public function downloadDocument(Order $order, string $documentType)
    {
        $generator = DocumentGeneratorFactory::create($documentType);
        $filePath = $generator->generate($order);
        $fileName = $generator->getFileName($order);

        return response()->download($filePath, $fileName)->deleteFileAfterSend();
    }

    /**
     * Генерирует документ и возвращает его как stream для просмотра в браузере
     *
     * @param Order $order
     * @param string $documentType
     * @return \Illuminate\Http\Response
     */
    public function streamDocument(Order $order, string $documentType)
    {
        $generator = DocumentGeneratorFactory::create($documentType);
        $filePath = $generator->generate($order);
        $fileName = $generator->getFileName($order);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
