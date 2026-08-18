<?php

namespace Tests\Feature;

use App\Enums\AssignmentType;
use App\Enums\WorkPermitStatus;
use App\Models\Department;
use App\Models\PermitType;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkPermit;
use App\Models\WorkPermitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkPermitCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $other;
    private Plant $plant;

    protected function setUp(): void
    {
        parent::setUp();
        $dept        = Department::create(['name' => 'Eng', 'code' => 'ENG']);
        $this->plant = Plant::create(['name' => 'Test Plant', 'code' => 'TST']);
        $this->user  = User::create(['name' => 'User A', 'domain' => 'LAGGRF', 'username' => 'usera', 'upn' => 'a@t.com', 'email' => 'a@t.com', 'is_active' => true, 'department_id' => $dept->id]);
        $this->other = User::create(['name' => 'User B', 'domain' => 'LAGGRF', 'username' => 'userb', 'upn' => 'b@t.com', 'email' => 'b@t.com', 'is_active' => true]);
        PermitType::create(['name' => 'Hot Work', 'code' => 'HOT']);
    }

    /** @test */
    public function authenticated_user_can_create_work_permit(): void
    {
        $response = $this->actingAs($this->user)->post(route('permits.store'), [
            'title'    => 'Test Hot Work',
            'plant_id' => $this->plant->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_permits', [
            'title'        => 'Test Hot Work',
            'requester_id' => $this->user->id,
            'status'       => WorkPermitStatus::DRAFT->value,
        ]);
    }

    /** @test */
    public function permit_number_is_auto_generated_on_create(): void
    {
        $this->actingAs($this->user)->post(route('permits.store'), [
            'title' => 'Auto Number Test',
        ]);

        $permit = WorkPermit::first();
        $this->assertMatchesRegularExpression('/^PTW-\d{4}-\d{6}$/', $permit->permit_number);
    }

    /** @test */
    public function creator_is_auto_assigned_as_requester(): void
    {
        $this->actingAs($this->user)->post(route('permits.store'), [
            'title' => 'Assignment Test',
        ]);

        $permit = WorkPermit::first();
        $this->assertDatabaseHas('work_permit_assignments', [
            'work_permit_id'  => $permit->id,
            'user_id'         => $this->user->id,
            'assignment_type' => AssignmentType::REQUESTER->value,
            'is_active'       => true,
        ]);
    }

    /** @test */
    public function user_can_view_own_permit(): void
    {
        $permit = $this->createPermitForUser($this->user);

        $response = $this->actingAs($this->user)->get(route('permits.show', $permit));
        $response->assertStatus(200);
        $response->assertSee($permit->permit_number);
    }

    /** @test */
    public function other_authenticated_user_can_also_view_permit_read_only(): void
    {
        // Kebijakan baru: visibilitas global agar operator lain tahu pekerjaan di plant
        $permit = $this->createPermitForUser($this->user);

        $response = $this->actingAs($this->other)->get(route('permits.show', $permit));
        $response->assertStatus(200);
    }

    /** @test */
    public function global_admin_can_view_any_permit(): void
    {
        $admin  = User::create(['name' => 'Admin', 'domain' => 'LAGGRF', 'username' => 'admin', 'upn' => 'admin@t.com', 'email' => 'admin@t.com', 'is_active' => true, 'is_global_admin' => true]);
        $permit = $this->createPermitForUser($this->user);

        $response = $this->actingAs($admin)->get(route('permits.show', $permit));
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_edit_draft_permit(): void
    {
        $permit = $this->createPermitForUser($this->user);

        $response = $this->actingAs($this->user)->put(route('permits.update', $permit), [
            'title'       => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('permits.show', $permit));
        $this->assertDatabaseHas('work_permits', ['id' => $permit->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function user_cannot_edit_permit_in_approval_status(): void
    {
        $permit = $this->createPermitForUser($this->user);
        $permit->update(['status' => WorkPermitStatus::APPROVAL->value]);

        $response = $this->actingAs($this->user)->put(route('permits.update', $permit), [
            'title' => 'Should Not Update',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function unassigned_user_cannot_edit_permit(): void
    {
        $permit = $this->createPermitForUser($this->user);

        $response = $this->actingAs($this->other)->put(route('permits.update', $permit), [
            'title' => 'Hijacked Title',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function only_draft_permits_can_be_soft_deleted(): void
    {
        $permit = $this->createPermitForUser($this->user);

        $this->actingAs($this->user)->delete(route('permits.destroy', $permit));

        $this->assertSoftDeleted('work_permits', ['id' => $permit->id]);
    }

    /** @test */
    public function non_draft_permits_cannot_be_deleted(): void
    {
        $permit = $this->createPermitForUser($this->user);
        $permit->update(['status' => WorkPermitStatus::ACTIVE->value]);

        $response = $this->actingAs($this->user)->delete(route('permits.destroy', $permit));

        // Returns 403 (or redirect if blocked in controller)
        $response->assertStatus(403);
        $this->assertDatabaseHas('work_permits', ['id' => $permit->id, 'deleted_at' => null]);
    }

    /** @test */
    public function permits_list_shows_all_permits_for_authenticated_users(): void
    {
        $myPermit    = $this->createPermitForUser($this->user);
        $otherPermit = $this->createPermitForUser($this->other);

        $response = $this->actingAs($this->user)->get(route('permits.index'));
        $response->assertStatus(200);
        $response->assertSee($myPermit->permit_number);
        $response->assertSee($otherPermit->permit_number); // Visibilitas global
    }

    /** @test */
    public function permits_list_can_be_filtered_by_status(): void
    {
        $draft     = $this->createPermitForUser($this->user);
        $approved  = $this->createPermitForUser($this->user);
        $approved->update(['status' => WorkPermitStatus::APPROVED->value]);

        $response = $this->actingAs($this->user)->get(route('permits.index', ['status' => 'draft']));
        $response->assertSee($draft->permit_number);
        $response->assertDontSee($approved->permit_number);
    }

    /** @test */
    public function create_permit_is_recorded_in_audit_log(): void
    {
        $this->actingAs($this->user)->post(route('permits.store'), [
            'title' => 'Audit Test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'CREATE_PERMIT',
            'record_type' => 'WorkPermit',
            'user_id'     => $this->user->id,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function createPermitForUser(User $user): WorkPermit
    {
        static $seq = 0;
        $seq++;
        $permit = WorkPermit::create([
            'permit_number' => 'PTW-2026-' . str_pad($seq, 6, '0', STR_PAD_LEFT),
            'title'         => 'Test Permit ' . $seq,
            'status'        => WorkPermitStatus::DRAFT->value,
            'plant_id'      => $this->plant->id,
            'requester_id'  => $user->id,
            'created_by'    => $user->id,
        ]);

        WorkPermitAssignment::create([
            'work_permit_id'  => $permit->id,
            'user_id'         => $user->id,
            'assignment_type' => AssignmentType::REQUESTER->value,
            'is_active'       => true,
            'assigned_by'     => $user->id,
            'assigned_at'     => now(),
        ]);

        return $permit;
    }
}
