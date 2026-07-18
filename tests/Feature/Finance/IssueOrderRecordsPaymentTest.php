<?php

namespace Tests\Feature\Finance;

use App\Application\Finance\Command\AcceptPaymentCommand;
use App\Application\Finance\Command\AcceptPaymentHandler;
use App\Application\Finance\Command\RecordPaymentForIssuedOrderCommand;
use App\Application\Finance\Command\RecordPaymentForIssuedOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\MarkOrderReadyCommand;
use App\Application\Order\Command\MarkOrderReadyHandler;
use App\Application\Pricing\Command\SetOrderWorkPricesCommand;
use App\Application\Pricing\Command\SetOrderWorkPricesHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Shared\Domain\DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsWorkshopFlows;
use Tests\TestCase;

final class IssueOrderRecordsPaymentTest extends TestCase
{
    use BuildsWorkshopFlows;
    use RefreshDatabase;

    public function test_issue_paid_order_creates_payment_from_work_prices(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '500.00');

        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'card'));

        $this->assertOrderStatus($flow['orderId'], OrderStatus::Issued);

        $payment = PaymentModel::query()->where('order_id', $flow['orderId'])->first();
        $this->assertNotNull($payment);
        $this->assertSame(500.0, (float) $payment->amount);
        $this->assertSame('card', $payment->method);
        $this->assertSame('RUB', $payment->currency);
        $this->assertMatchesRegularExpression('/^PMT-\d{2}-\d+$/', (string) $payment->number);

        $again = app(PaymentRepository::class)->findByOrderId(new OrderId($flow['orderId']));
        $this->assertNotNull($again);
        $this->assertSame(1, PaymentModel::query()->where('order_id', $flow['orderId'])->count());
    }

    public function test_issue_is_idempotent_for_payment_via_listener_replay(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '250.00');

        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'cash'));

        app(RecordPaymentForIssuedOrderHandler::class)->handle(
            new RecordPaymentForIssuedOrderCommand($flow['orderId'], 'cash'),
        );

        $this->assertSame(1, PaymentModel::query()->where('order_id', $flow['orderId'])->count());
    }

    public function test_manual_accept_payment_forbidden_after_issue(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '100.00');
        app(IssueOrderHandler::class)->handle(new IssueOrderCommand($flow['orderId'], 'cash'));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Issued orders are settled automatically');

        app(AcceptPaymentHandler::class)->handle(new AcceptPaymentCommand(
            app(EntityIdGenerator::class)->next('payment')->value,
            $flow['orderId'],
            '999.00',
            'cash',
        ));
    }

    public function test_manual_accept_payment_forbidden_when_payment_exists(): void
    {
        $flow = $this->bringSharpeningOrderToReady(baseAmount: '100.00');
        // Payment without issuing: create via Accept while status is Ready
        app(AcceptPaymentHandler::class)->handle(new AcceptPaymentCommand(
            app(EntityIdGenerator::class)->next('payment')->value,
            $flow['orderId'],
            '100.00',
            'cash',
        ));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Payment for this order already exists');

        app(AcceptPaymentHandler::class)->handle(new AcceptPaymentCommand(
            app(EntityIdGenerator::class)->next('payment')->value,
            $flow['orderId'],
            '50.00',
            'card',
        ));
    }

    /**
     * @return array{orderId: string, masterId: int, taskId: int, orderItemId: int}
     */
    private function bringSharpeningOrderToReady(string $baseAmount): array
    {
        $master = $this->createMaster('issue-payment-'.uniqid('', true).'@test.local');
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
