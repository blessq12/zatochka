<?php

namespace Tests\Unit\Domain\Workshop;

use App\Domain\Workshop\VO\ProductionStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductionStatusTransitionTest extends TestCase
{
    #[DataProvider('allowedTransitions')]
    public function test_allows_expected_transitions(ProductionStatus $from, ProductionStatus $to): void
    {
        $this->assertTrue($from->canTransitionTo($to));
    }

    #[DataProvider('forbiddenTransitions')]
    public function test_forbids_invalid_transitions(ProductionStatus $from, ProductionStatus $to): void
    {
        $this->assertFalse($from->canTransitionTo($to));
    }

    /** @return iterable<string, array{ProductionStatus, ProductionStatus}> */
    public static function allowedTransitions(): iterable
    {
        yield 'queued → master' => [ProductionStatus::Queued, ProductionStatus::MasterAssigned];
        yield 'master → in_work' => [ProductionStatus::MasterAssigned, ProductionStatus::InWork];
        yield 'in_work → waiting' => [ProductionStatus::InWork, ProductionStatus::WaitingParts];
        yield 'in_work → work_completed' => [ProductionStatus::InWork, ProductionStatus::WorkCompleted];
        yield 'work_completed → completed' => [ProductionStatus::WorkCompleted, ProductionStatus::Completed];
        yield 'completed → rework' => [ProductionStatus::Completed, ProductionStatus::InWork];
        yield 'completed → rejected' => [ProductionStatus::Completed, ProductionStatus::Rejected];
    }

    /** @return iterable<string, array{ProductionStatus, ProductionStatus}> */
    public static function forbiddenTransitions(): iterable
    {
        yield 'queued → completed' => [ProductionStatus::Queued, ProductionStatus::Completed];
        yield 'rejected → in_work' => [ProductionStatus::Rejected, ProductionStatus::InWork];
        yield 'queued → in_work' => [ProductionStatus::Queued, ProductionStatus::InWork];
    }
}
