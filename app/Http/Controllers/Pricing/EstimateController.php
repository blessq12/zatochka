<?php

namespace App\Http\Controllers\Pricing;

use App\Application\Pricing\Command\ApplyDiscountCommand;
use App\Application\Pricing\Command\ApplyDiscountHandler;
use App\Application\Pricing\Command\CalculatePriceCommand;
use App\Application\Pricing\Command\CalculatePriceHandler;
use App\Application\Pricing\Command\CreateEstimateCommand;
use App\Application\Pricing\Command\CreateEstimateHandler;
use App\Application\Pricing\Query\GetEstimateByIdHandler;
use App\Application\Pricing\Query\GetEstimateByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EstimateController extends Controller
{
    public function __construct(
        private CreateEstimateHandler $createEstimate,
        private CalculatePriceHandler $calculatePrice,
        private ApplyDiscountHandler $applyDiscount,
        private GetEstimateByIdHandler $getEstimateById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orderItemId' => ['required', 'integer'],
            'estimatedAmount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $estimateId = $this->ids->next('estimate')->value;

        $this->createEstimate->handle(new CreateEstimateCommand(
            $estimateId,
            (int) $data['orderItemId'],
            (string) $data['estimatedAmount'],
            $data['currency'] ?? 'RUB',
        ));

        return $this->created($this->getEstimateById->handle(new GetEstimateByIdQuery($estimateId)));
    }

    public function show(int $estimateId): JsonResponse
    {
        $estimate = $this->getEstimateById->handle(new GetEstimateByIdQuery($estimateId));

        if ($estimate === null) {
            return response()->json(['message' => 'Estimate not found.'], 404);
        }

        return $this->ok($estimate);
    }

    public function calculate(Request $request, int $estimateId): JsonResponse
    {
        $data = $request->validate([
            'baseAmount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $this->calculatePrice->handle(new CalculatePriceCommand(
            $estimateId,
            $this->ids->next('item_price')->value,
            (string) $data['baseAmount'],
            $data['currency'] ?? 'RUB',
        ));

        return $this->ok($this->getEstimateById->handle(new GetEstimateByIdQuery($estimateId)));
    }

    public function applyDiscount(Request $request, int $estimateId): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:percentage,fixed'],
            'value' => ['required', 'numeric'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->applyDiscount->handle(new ApplyDiscountCommand(
            $estimateId,
            $this->ids->next('discount')->value,
            $data['type'],
            (string) $data['value'],
            $data['reason'] ?? null,
        ));

        return $this->ok($this->getEstimateById->handle(new GetEstimateByIdQuery($estimateId)));
    }
}
