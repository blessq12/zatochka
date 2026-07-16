<?php

namespace App\Http\Controllers\Finance;

use App\Application\Finance\Command\AcceptPaymentCommand;
use App\Application\Finance\Command\AcceptPaymentHandler;
use App\Application\Finance\Command\CreateRefundCommand;
use App\Application\Finance\Command\CreateRefundHandler;
use App\Application\Finance\Query\GetPaymentByIdHandler;
use App\Application\Finance\Query\GetPaymentByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    public function __construct(
        private AcceptPaymentHandler $acceptPayment,
        private CreateRefundHandler $createRefund,
        private GetPaymentByIdHandler $getPaymentById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['required', 'string', 'size:32'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'string', 'in:cash,card,transfer'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $paymentId = $this->ids->next('payment')->value;

        $this->acceptPayment->handle(new AcceptPaymentCommand(
            $paymentId,
            (string) $data['orderId'],
            (string) $data['amount'],
            $data['method'],
            $data['currency'] ?? 'RUB',
        ));

        return $this->created($this->getPaymentById->handle(new GetPaymentByIdQuery($paymentId)));
    }

    public function show(int $paymentId): JsonResponse
    {
        $payment = $this->getPaymentById->handle(new GetPaymentByIdQuery($paymentId));

        if ($payment === null) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        return $this->ok($payment);
    }

    public function refund(Request $request, int $paymentId): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->createRefund->handle(new CreateRefundCommand(
            $paymentId,
            $this->ids->next('refund')->value,
            (string) $data['amount'],
            $data['currency'] ?? 'RUB',
            $data['reason'] ?? null,
        ));

        return $this->ok($this->getPaymentById->handle(new GetPaymentByIdQuery($paymentId)));
    }
}
