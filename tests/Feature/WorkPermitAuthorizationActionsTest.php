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
use App\Models\WorkPermitApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkPermitAuthorizationActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;
    private User $approver;
    private User $releaseUser;
    private User $otherUser;
    private WorkPermit $permit;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $plant = Plant::create(['name' => 'Power Plant', 'code' => 'PP']);

        $this->requester   = User::create(['name' => 'Requester Budi',   'domain' => 'LAGGRF', 'username' => 'budi',   'upn' => 'budi@t.com',   'email' => 'budi@t.com',   'is_active' => true, 'department_id' => $dept->id]);
        $this->approver    = User::create(['name' => 'Approver Andi',    'domain' => 'LAGGRF', 'username' => 'andi',   'upn' => 'andi@t.com',   'email' => 'andi@t.com',   'is_active' => true]);
        $this->releaseUser = User::create(['name' => 'Release Candra',   'domain' => 'LAGGRF', 'username' => 'candra', 'upn' => 'candra@t.com', 'email' => 'candra@t.com', 'is_active' => true]);
        $this->otherUser   = User::create(['name' => 'Iseng User',       'domain' => 'LAGGRF', 'username' => 'iseng',  'upn' => 'iseng@t.com',  'email' => 'iseng@t.com',  'is_active' => true]);

        // Create permit
        $this->permit = WorkPermit::create([
            'permit_number' => 'PTW-2026-000123',
            'title'         => 'Pekerjaan Belt Press',
            'status'        => WorkPermitStatus::APPROVAL->value,
            'plant_id'      => $plant->id,
            'requester_id'  => $this->requester->id,
            'created_by'    => $this->requester->id,
        ]);

        // Setup Assignments
        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->requester->id,   'assignment_type' => AssignmentType::REQUESTER->value, 'is_active' => true, 'assigned_at' => now()]);
        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->approver->id,    'assignment_type' => AssignmentType::APPROVER->value,  'approval_order' => 1, 'is_active' => true, 'assigned_at' => now()]);
        WorkPermitAssignment::create(['work_permit_id' => $this->permit->id, 'user_id' => $this->releaseUser->id, 'assignment_type' => AssignmentType::RELEASE->value,   'is_active' => true, 'assigned_at' => now()]);

        // Setup Approval Snapshot (active cycle 1)
        WorkPermitApproval::create([
            'work_permit_id' => $this->permit->id,
            'approver_id'    => $this->approver->id,
            'approval_order' => 1,
            'cycle_number'   => 1,
            'status'         => ApprovalStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function pending_approver_can_approve_via_http(): void
    {
        $response = $this->actingAs($this->approver)->post(route('permits.approve', $this->permit), [
            'comment' => 'Approved, check gas test.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_permit_approvals', [
            'work_permit_id' => $this->permit->id,
            'approver_id'    => $this->approver->id,
            'status'         => ApprovalStatus::APPROVED->value,
        ]);
    }

    /** @test */
    public function unauthorized_user_trying_to_approve_returns_403(): void
    {
        $response = $this->actingAs($this->otherUser)->post(route('permits.approve', $this->permit), [
            'comment' => 'I am approving this illegally',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function requester_trying_to_approve_own_permit_returns_403(): void
    {
        $response = $this->actingAs($this->requester)->post(route('permits.approve', $this->permit), [
            'comment' => 'Self approval',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthorized_user_trying_to_reject_returns_403(): void
    {
        $response = $this->actingAs($this->otherUser)->post(route('permits.reject', $this->permit), [
            'comment' => 'Illegal rejection',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthorized_user_trying_to_release_returns_403(): void
    {
        // Set to APPROVED status so release action is workflow-valid
        $this->permit->update(['status' => WorkPermitStatus::APPROVED->value]);

        $response = $this->actingAs($this->otherUser)->post(route('permits.release', $this->permit));

        $response->assertStatus(403);
    }

    /** @test */
    public function assigned_release_user_can_release_via_http(): void
    {
        // APPROVED -> RELEASE (via release action)
        $this->permit->update(['status' => WorkPermitStatus::APPROVED->value]);

        $response = $this->actingAs($this->releaseUser)->post(route('permits.release', $this->permit));

        $response->assertRedirect();
        $this->assertEquals(WorkPermitStatus::RELEASE->value, $this->permit->fresh()->getStatusEnum()->value);
    }
}
