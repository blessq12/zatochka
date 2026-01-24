<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Document\DocumentGenerationService;
use App\Services\Document\Factories\DocumentGeneratorFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentGenerationService $documentService
    ) {
    }

    /**
     * Генерирует документ и возвращает его для скачивания
     *
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function download(Request $request, $order)
    {
        $order = $order instanceof Order ? $order : Order::findOrFail($order);
        
        $request->validate([
            'type' => 'required|in:' . implode(',', [
                DocumentGeneratorFactory::TYPE_ACCEPTANCE,
                DocumentGeneratorFactory::TYPE_ISSUANCE,
            ]),
        ]);

        return $this->documentService->downloadDocument(
            $order,
            $request->input('type')
        );
    }

    /**
     * Генерирует документ и возвращает его для просмотра в браузере с автоматической печатью
     *
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function view(Request $request, $order)
    {
        $order = $order instanceof Order ? $order : Order::findOrFail($order);
        
        $request->validate([
            'type' => 'required|in:' . implode(',', [
                DocumentGeneratorFactory::TYPE_ACCEPTANCE,
                DocumentGeneratorFactory::TYPE_ISSUANCE,
            ]),
        ]);

        // URL для получения PDF
        $pdfUrl = route('api.orders.documents.pdf', [
            'order' => $order->id,
            'type' => $request->input('type'),
        ]);
        
        // Возвращаем HTML страницу с iframe и автоматической печатью
        return response()->view('documents.print', [
            'pdfUrl' => $pdfUrl,
        ]);
    }

    /**
     * Генерирует и возвращает PDF файл
     *
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function pdf(Request $request, $order)
    {
        $order = $order instanceof Order ? $order : Order::findOrFail($order);
        
        $request->validate([
            'type' => 'required|in:' . implode(',', [
                DocumentGeneratorFactory::TYPE_ACCEPTANCE,
                DocumentGeneratorFactory::TYPE_ISSUANCE,
            ]),
        ]);

        return $this->documentService->streamDocument(
            $order,
            $request->input('type')
        );
    }

    /**
     * Генерирует документ и сохраняет его в MediaLibrary
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request, $order)
    {
        $order = $order instanceof Order ? $order : Order::findOrFail($order);
        
        $request->validate([
            'type' => 'required|in:' . implode(',', [
                DocumentGeneratorFactory::TYPE_ACCEPTANCE,
                DocumentGeneratorFactory::TYPE_ISSUANCE,
            ]),
        ]);

        $result = $this->documentService->generateDocument(
            $order,
            $request->input('type'),
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Документ успешно сгенерирован и сохранен',
            'data' => $result,
        ]);
    }
}
