<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\AssignmentType;
use App\Enums\WorkPermitStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkPermit;
use App\Models\WorkPermitAssignment;
use App\Services\WorkPermitWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private WorkPermitWorkflowService $workflow;
    private User $requester;
    private User $approver;
    private User $releaseUser;
    private User $otherUser;
    private WorkPermit $permit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = app(WorkPermitWorkflowService::class);

        $dept  = Department::create(['name' => 'Eng', 'code' => 'ENG']);
        $plant = Plant::create(['name' => 'Test Plant', 'code' => 'TST']);

        $this->requester   = User::create(['name' => 'Requester',  'domain' => 'LAGGRF', 'username' => 'req',      'upn' => 'req@test.com',      'email' => 'req@test.com',      'is_active' => true, 'department_id' => $dept->id]);
        $this->approver    = User::create(['name' => 'Approver',   'domain' => 'LAGGRF', 'username' => 'approver', 'upn' => 'approver@test.com', 'email' => 'approver@test.com', 'is_active' => true, 'department_id' => $dept->id]);
        $this->releaseUser = User::create(['name' => 'Release',    'domain' => 'LAGGRF', 'username' => 'release',  'upn' => 'release@test.com',  'email' => 'release@test.com',  'is_active' => true, 'department_id' => $dept->id]);
        $this->otherUser   = User::create(['name' => 'Other User', 'domain' => 'LAGGRF', 'username' => 'other',    'upn' => 'other@test.com',    'email' => 'other@test.com',    'is_active' => true, 'department_id' => $dept->id]);

        $this->permit = WorkPermit::create([
            'permit_number' => 'PTW-2026-000001',
            'title'         => 'Test Hot Work',
            'status'        => WorkPermitStatus::DRAFT->value,
            'plant_id'      => $plant->id,
            'requester_id'  => $this->requester->id,
            'created_by'    => $this->requester->id,
        ]);

        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->requester->id,   'assignment_type' => AssignmentType::REQUESTER->value, 'is_active' => true, 'assigned_at' => now()]);
        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->approver->id,    'assignment_type' => AssignmentType::APPROVER->value,  'approval_order' => 1, 'is_active' => true, 'assigned_at' => now()]);
        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->releaseUser->id, 'assignment_type' => AssignmentType::RELEASE->value,   'is_active' => true, 'assigned_at' => now()]);
    }

    private function freshPermit(): WorkPermit
    {
        return WorkPermit::find($this->permit->id);
    }

    /** @test */
    public function requester_can_submit_from_draft(): void
    {
        $this->workflow->submit($this->permit, $this->requester);

        $permit = $this->freshPermit();
        $this->assertEquals(WorkPermitStatus::SUBMITTED->value, $permit->getStatusEnum()->value);
        $this->assertCount(1, $permit->statusHistories);
    }

    /** @test */
    public function permit_can_be_sent_to_approval_and_creates_approval_records(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);

        $permit = $this->freshPermit();
        $this->assertEquals(WorkPermitStatus::APPROVAL->value, $permit->getStatusEnum()->value);

        $approvals = $permit->approvals()->get();
        $this->assertCount(1, $approvals);
        $this->assertEquals(ApprovalStatus::PENDING->value, $approvals->first()->status->value);
        $this->assertEquals($this->approver->id, $approvals->first()->approver_id);
    }

    /** @test */
    public function approver_can_approve_permit(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);
        $this->workflow->approve($this->permit, $this->approver, 'Looks good');

        $permit = $this->freshPermit();
        $this->assertEquals(WorkPermitStatus::APPROVED->value, $permit->getStatusEnum()->value);

        $approval = $permit->approvals()->first();
        $this->assertEquals(ApprovalStatus::APPROVED->value, $approval->status->value);
        $this->assertEquals('Looks good', $approval->comment);
        $this->assertNotNull($approval->approved_at);
    }

    /** @test */
    public function non_approver_cannot_approve(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);

        $this->expectException(\RuntimeException::class);
        $this->workflow->approve($this->permit, $this->otherUser);
    }

    /** @test */
    public function requester_cannot_approve_own_permit(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);

        $this->expectException(\RuntimeException::class);
        $this->workflow->approve($this->permit, $this->requester);
    }

    /** @test */
    public function approver_can_reject_and_permit_goes_to_rejected(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);
        $this->workflow->reject($this->permit, $this->approver, 'Gas test missing');

        $permit = $this->freshPermit();
        $this->assertEquals(WorkPermitStatus::REJECTED->value, $permit->getStatusEnum()->value);

        $approval = $permit->approvals()->first();
        $this->assertEquals(ApprovalStatus::REJECTED->value, $approval->status->value);
        $this->assertNotNull($approval->rejected_at);
    }

    /** @test */
    public function revision_creates_new_cycle_and_preserves_old_history(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);
        $this->workflow->reject($this->permit, $this->approver, 'Missing docs');

        // Cycle 1 preserved
        $cycle1 = WorkPermit::find($this->permit->id)->approvals()->where('cycle_number', 1)->first();
        $this->assertEquals(ApprovalStatus::REJECTED->value, $cycle1->status->value);

        // Revise and re-submit
        $this->workflow->revise($this->permit, $this->requester);
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);

        // Cycle 2 created fresh
        $cycle2 = WorkPermit::find($this->permit->id)->approvals()->where('cycle_number', 2)->first();
        $this->assertNotNull($cycle2);
        $this->assertEquals(ApprovalStatus::PENDING->value, $cycle2->status->value);

        // Cycle 1 still unchanged
        $cycle1After = WorkPermit::find($this->permit->id)->approvals()->where('cycle_number', 1)->first();
        $this->assertEquals(ApprovalStatus::REJECTED->value, $cycle1After->status->value);
    }

    /** @test */
    public function full_happy_path_draft_to_closed(): void
    {
        $this->workflow->submit($this->permit, $this->requester);
        $this->workflow->sendToApproval($this->permit, $this->requester);
        $this->workflow->approve($this->permit, $this->approver);
        $this->workflow->release($this->permit, $this->releaseUser);
        $this->workflow->activate($this->permit, $this->releaseUser);
        $this->workflow->finish($this->permit, $this->requester);
        $this->workflow->close($this->permit, $this->releaseUser);

        $permit = $this->freshPermit();
        $this->assertEquals(WorkPermitStatus::CLOSED->value, $permit->getStatusEnum()->value);

        $histories = $permit->statusHistories()->get();
        $this->assertCount(7, $histories);
    }

    /** @test */
    public function invalid_transition_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->workflow->close($this->permit, $this->requester);
    }

    /** @test */
    public function audit_log_is_recorded_for_every_transition(): void
    {
        $this->workflow->submit($this->permit, $this->requester);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'STATUS_CHANGE',
            'record_type' => 'WorkPermit',
            'record_id'   => $this->permit->id,
            'old_value'   => 'draft',
            'new_value'   => 'submitted',
        ]);
    }
}
