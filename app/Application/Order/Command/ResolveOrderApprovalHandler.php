<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Aggregate\OrderComment;
use App\Domain\Order\OrderCommentKind;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderCommentRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Domain\Workshop\WorkshopJobStatus;
use App\Shared\Domain\DomainException;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderIssued;
use Illuminate\Support\Facades\DB;

final readonly class ResolveOrderApprovalHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderCommentRepository $comments,
        private OrderResponseAssembler $assembler,
        private EventBus $events,
        private WorkshopJobRepository $workshopJobs,
    ) {}

    public function handle(int $orderId, int $managerId, string $status, string $body): OrderResponse
    {
        return DB::transaction(function () use ($orderId, $managerId, $status, $body): OrderResponse {
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                throw new DomainException('Order not found.');
            }

            if ($order->status() !== OrderStatus::Approval) {
                throw new DomainException('Order is not in approval.');
            }

            $target = OrderStatus::tryFrom($status)
                ?? throw new DomainException('Invalid status.');

            if (! in_array($target, [OrderStatus::InProgress, OrderStatus::Issued], true)) {
                throw new DomainException('Approval can resolve only to in_progress or issued.');
            }

            $order->transitionTo($target);
            $saved = $this->orders->save($order);

            if ($target === OrderStatus::Issued) {
                $this->closeOpenWorkshopJobWithoutWorksEvent($orderId);
                $this->events->publish(new OrderIssued(
                    $orderId,
                    $saved->billingType()->value,
                ));
            }

            $this->comments->save(OrderComment::create(
                $orderId,
                'managers',
                $managerId,
                $body,
                OrderCommentKind::ApprovalResult,
            ));

            return $this->assembler->assemble($saved, true);
        });
    }

    private function closeOpenWorkshopJobWithoutWorksEvent(int $orderId): void
    {
        $job = $this->workshopJobs->findByOrderId($orderId);
        if ($job === null || $job->status() !== WorkshopJobStatus::Open) {
            return;
        }

        $job->complete();
        $this->workshopJobs->save($job);
    }
}
