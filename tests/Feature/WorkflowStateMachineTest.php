<?php

namespace Tests\Feature;

use App\Enums\WorkPermitStatus;
use Tests\TestCase;

class WorkflowStateMachineTest extends TestCase
{
    /** @test */
    public function valid_transitions_are_allowed(): void
    {
        $validTransitions = [
            [WorkPermitStatus::DRAFT,               WorkPermitStatus::SUBMITTED],
            [WorkPermitStatus::SUBMITTED,            WorkPermitStatus::APPROVAL],
            [WorkPermitStatus::APPROVAL,             WorkPermitStatus::APPROVED],
            [WorkPermitStatus::APPROVAL,             WorkPermitStatus::REJECTED],
            [WorkPermitStatus::REJECTED,             WorkPermitStatus::REVISION],
            [WorkPermitStatus::REVISION,             WorkPermitStatus::SUBMITTED],
            [WorkPermitStatus::APPROVED,             WorkPermitStatus::RELEASE],
            [WorkPermitStatus::RELEASE,              WorkPermitStatus::ACTIVE],
            [WorkPermitStatus::ACTIVE,               WorkPermitStatus::FINISH_NOTIFICATION],
            [WorkPermitStatus::FINISH_NOTIFICATION,  WorkPermitStatus::CLOSED],
        ];

        foreach ($validTransitions as [$from, $to]) {
            $this->assertTrue(
                $from->canTransitionTo($to),
                "Expected {$from->value} → {$to->value} to be valid"
            );
        }
    }

    /** @test */
    public function invalid_transitions_are_rejected(): void
    {
        $invalidTransitions = [
            [WorkPermitStatus::DRAFT,    WorkPermitStatus::CLOSED],
            [WorkPermitStatus::DRAFT,    WorkPermitStatus::ACTIVE],
            [WorkPermitStatus::DRAFT,    WorkPermitStatus::APPROVED],
            [WorkPermitStatus::CLOSED,   WorkPermitStatus::APPROVED],
            [WorkPermitStatus::CLOSED,   WorkPermitStatus::DRAFT],
            [WorkPermitStatus::APPROVED, WorkPermitStatus::DRAFT],
            [WorkPermitStatus::ACTIVE,   WorkPermitStatus::APPROVAL],
        ];

        foreach ($invalidTransitions as [$from, $to]) {
            $this->assertFalse(
                $from->canTransitionTo($to),
                "Expected {$from->value} → {$to->value} to be INVALID"
            );
        }
    }

    /** @test */
    public function editable_statuses_are_correct(): void
    {
        $this->assertTrue(WorkPermitStatus::DRAFT->isEditable());
        $this->assertTrue(WorkPermitStatus::REVISION->isEditable());

        $this->assertFalse(WorkPermitStatus::SUBMITTED->isEditable());
        $this->assertFalse(WorkPermitStatus::APPROVAL->isEditable());
        $this->assertFalse(WorkPermitStatus::APPROVED->isEditable());
        $this->assertFalse(WorkPermitStatus::CLOSED->isEditable());
        $this->assertFalse(WorkPermitStatus::ACTIVE->isEditable());
    }

    /** @test */
    public function terminal_status_has_no_transitions(): void
    {
        $this->assertEmpty(WorkPermitStatus::CLOSED->allowedTransitions());
        $this->assertTrue(WorkPermitStatus::CLOSED->isTerminal());
    }
}
