<?php

namespace Tests\Feature\Finance;

use App\Application\Finance\Command\CreateRefundCommand;
use App\Application\Finance\Command\CreateRefundHandler;
use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Application\Finance\Query\GetCashDeskSummaryHandler;
use App\Application\Finance\Query\GetCashDeskSummaryQuery;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\MarkOrderReadyCommand;
use App\Application\Order\Command\MarkOrderReadyHandler;
use App\Application\Pricing\Command\SetOrderWorkPricesCommand;
use App\Application\Pricing\Command\SetOrderWorkPricesHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class CashDeskSoTTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_cash_payment_on_issue_registers_cash_in(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '300.00');

        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'cash'));

        $payment = PaymentModel::query()->where('order_id', $flow['orderId'])->first();
        $this->assertNotNull($payment);

        $operation = CashOperationModel::query()->where('payment_id', $payment->id)->first();
        $this->assertNotNull($operation);
        $this->assertSame(CashOperationType::In->value, $operation->type);
        $this->assertSame(PaymentMethod::Cash->value, $operation->payment_method);
        $this->assertSame(300.0, (float) $operation->amount);
        $this->assertMatchesRegularExpression('/^Оплата заказа ORD-\d{2}-\d+$/', (string) $operation->comment);
    }

    public function test_card_payment_registers_cash_in_with_card_method(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '300.00');

        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'card'));

        $payment = PaymentModel::query()->where('order_id', $flow['orderId'])->first();
        $this->assertNotNull($payment);

        $operation = CashOperationModel::query()->where('payment_id', $payment->id)->first();
        $this->assertNotNull($operation);
        $this->assertSame(CashOperationType::In->value, $operation->type);
        $this->assertSame(PaymentMethod::Card->value, $operation->payment_method);
        $this->assertSame(300.0, (float) $operation->amount);
    }

    public function test_cash_in_from_payment_is_idempotent(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '150.00');
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'cash'));

        $payment = PaymentModel::query()->where('order_id', $flow['orderId'])->firstOrFail();

        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
            app(EntityIdGenerator::class)->next('cash_operation')->value,
            CashOperationType::In->value,
            '150.00',
            'RUB',
            'replay',
            (int) $payment->id,
            paymentMethod: PaymentMethod::Cash->value,
        ));

        $this->assertSame(1, CashOperationModel::query()->where('payment_id', $payment->id)->count());
    }

    public function test_card_refund_registers_cash_out(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '200.00');
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'card'));

        $payment = PaymentModel::query()->where('order_id', $flow['orderId'])->firstOrFail();
        $refundId = app(EntityIdGenerator::class)->next('refund')->value;

        app(CreateRefundHandler::class)->handle(new CreateRefundCommand(
            (int) $payment->id,
            $refundId,
            '50.00',
            'RUB',
            'partial',
        ));

        $operation = CashOperationModel::query()->where('refund_id', $refundId)->first();
        $this->assertNotNull($operation);
        $this->assertSame(CashOperationType::Out->value, $operation->type);
        $this->assertSame(PaymentMethod::Card->value, $operation->payment_method);
        $this->assertSame(50.0, (float) $operation->amount);
        $this->assertMatchesRegularExpression('/^Возврат по заказу ORD-\d{2}-\d+$/', (string) $operation->comment);
    }

    public function test_cash_desk_summary_filters_by_payment_method(): void
    {
        $ids = app(EntityIdGenerator::class);

        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
            $ids->next('cash_operation')->value,
            CashOperationType::In->value,
            '100.00',
            'RUB',
            'cash in',
            paymentMethod: PaymentMethod::Cash->value,
        ));
        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
            $ids->next('cash_operation')->value,
            CashOperationType::In->value,
            '70.00',
            'RUB',
            'card in',
            paymentMethod: PaymentMethod::Card->value,
        ));
        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
            $ids->next('cash_operation')->value,
            CashOperationType::Out->value,
            '40.00',
            'RUB',
            'cash out',
            paymentMethod: PaymentMethod::Cash->value,
        ));

        $from = new DateTimeImmutable('today');
        $to = new DateTimeImmutable('tomorrow');

        $all = app(GetCashDeskSummaryHandler::class)->handle(
            new GetCashDeskSummaryQuery($from, $to),
        );
        $this->assertSame('170.00', $all->inTotal);
        $this->assertSame('40.00', $all->outTotal);
        $this->assertSame('130.00', $all->netTotal);

        $cashOnly = app(GetCashDeskSummaryHandler::class)->handle(
            new GetCashDeskSummaryQuery($from, $to, paymentMethod: PaymentMethod::Cash->value),
        );
        $this->assertSame('100.00', $cashOnly->inTotal);
        $this->assertSame('40.00', $cashOnly->outTotal);
        $this->assertSame('60.00', $cashOnly->netTotal);
        $this->assertCount(2, $cashOnly->recentOperations);
    }

    /**
     * @return array{orderId: string, masterId: int, taskId: int, orderItemId: int}
     */
    private function bringSharpeningOrderToReady(string $baseAmount): array
    {
        $master = $this->createMaster('cash-desk-'.uniqid('', true).'@test.local');
        $flow = $this->createSharpeningOrderWithMaster($master);

        $this->startWork($flow['taskId']);
        $this->addSharpeningWork($flow['taskId'], $flow['masterId'], $flow['orderItemId']);
        $this->finishTask($flow['taskId']);

        $workId = (int) PerformedWorkModel::query()
            ->where('production_task_id', $flow['taskId'])
            ->value('id');

        app(SetOrderWorkPricesHandler::class)->handle(new SetOrderWorkPricesCommand(
            $flow['orderId'],
            [['performed_work_id' => $workId, 'base_amount' => $baseAmount]],
        ));

        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand($flow['orderId']));
        $this->assertOrderStatus($flow['orderId'], OrderStatus::Ready);

        return $flow;
    }
}
