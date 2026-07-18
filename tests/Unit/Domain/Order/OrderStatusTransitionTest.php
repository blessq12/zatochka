<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\VO\OrderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OrderStatusTransitionTest extends TestCase
{
    #[DataProvider('allowedTransitions')]
    public function test_allows_expected_transitions(OrderStatus $from, OrderStatus $to): void
    {
        $this->assertTrue($from->canTransitionTo($to));
    }

    #[DataProvider('forbiddenTransitions')]
    public function test_forbids_invalid_transitions(OrderStatus $from, OrderStatus $to): void
    {
        $this->assertFalse($from->canTransitionTo($to));
    }

    public function test_terminal_statuses(): void
    {
        $this->assertTrue(OrderStatus::Cancelled->isTerminal());
        $this->assertTrue(OrderStatus::Closed->isTerminal());
        $this->assertTrue(OrderStatus::Issued->isTerminal());
        $this->assertFalse(OrderStatus::WorksCompleted->isTerminal());
    }

    /** @return iterable<string, array{OrderStatus, OrderStatus}> */
    public static function allowedTransitions(): iterable
    {
        yield 'created → master_assigned' => [OrderStatus::Created, OrderStatus::MasterAssigned];
        yield 'created → reception' => [OrderStatus::Created, OrderStatus::ReceptionCompleted];
        yield 'created → cancelled' => [OrderStatus::Created, OrderStatus::Cancelled];
        yield 'master → in_progress' => [OrderStatus::MasterAssigned, OrderStatus::InProgress];
        yield 'in_progress → works_completed' => [OrderStatus::InProgress, OrderStatus::WorksCompleted];
        yield 'works_completed → ready' => [OrderStatus::WorksCompleted, OrderStatus::Ready];
        yield 'works_completed → rework' => [OrderStatus::WorksCompleted, OrderStatus::InProgress];
        yield 'ready → issued' => [OrderStatus::Ready, OrderStatus::Issued];
    }

    /** @return iterable<string, array{OrderStatus, OrderStatus}> */
    public static function forbiddenTransitions(): iterable
    {
        yield 'created → ready' => [OrderStatus::Created, OrderStatus::Ready];
        yield 'created → works_completed' => [OrderStatus::Created, OrderStatus::WorksCompleted];
        yield 'issued → ready' => [OrderStatus::Issued, OrderStatus::Ready];
        yield 'cancelled → in_progress' => [OrderStatus::Cancelled, OrderStatus::InProgress];
        yield 'ready → works_completed' => [OrderStatus::Ready, OrderStatus::WorksCompleted];
        yield 'master → cancelled' => [OrderStatus::MasterAssigned, OrderStatus::Cancelled];
        yield 'in_progress → cancelled' => [OrderStatus::InProgress, OrderStatus::Cancelled];
        yield 'works_completed → cancelled' => [OrderStatus::WorksCompleted, OrderStatus::Cancelled];
        yield 'ready → cancelled' => [OrderStatus::Ready, OrderStatus::Cancelled];
    }
}
