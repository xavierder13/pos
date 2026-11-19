<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use App\Support\Rbac;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

class ApprovalTest extends ApiTestCase
{
    private Role $managers;

    private User $manager;

    private User $director;

    private User $requester;

    private User $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->managers = Role::create(['name' => 'Manager', 'guard_name' => Rbac::GUARD]);
        $this->manager = User::factory()->create(['name' => 'Manny Manager']);
        $this->manager->assignRole($this->managers);
        $this->director = User::factory()->create(['name' => 'Dee Director']);
        $this->requester = User::factory()->create(['name' => 'Rae Requester']);
        $clerk = Role::create(['name' => 'Clerk', 'guard_name' => Rbac::GUARD]);
        $clerk->syncPermissions(Permission::whereIn('name', ['user-list', 'user-edit'])->get());
        $this->requester->assignRole($clerk);
        $this->subject = User::factory()->create(['name' => 'Subject Record']);

        // level 1: anyone with Manager · level 2: the director
        $this->actingAsAdmin();
        $this->postJson('/api/approval-flows', ['name' => 'User changes', 'record_type' => 'user', 'levels' => [
            ['approver_type' => 'role', 'approver_id' => $this->managers->id],
            ['approver_type' => 'user', 'approver_id' => $this->director->id],
        ]])->assertCreated();
    }

    private function submit(): int
    {
        Sanctum::actingAs($this->requester);

        return $this->postJson('/api/approvals/submit', ['type' => 'user', 'id' => $this->subject->id, 'remarks' => 'Please check'])
            ->assertCreated()->assertJsonPath('data.status', 'pending')->json('data.id');
    }

    public function test_two_level_approval_with_notifications(): void
    {
        Notification::fake();
        $id = $this->submit();
        Notification::assertSentTo($this->manager, ApprovalNotification::class);
        Notification::assertNotSentTo($this->director, ApprovalNotification::class);

        // the manager sees it waiting and approves level 1
        Sanctum::actingAs($this->manager);
        $this->getJson('/api/approvals?scope=awaiting')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.can_decide', true);
        $this->postJson("/api/approvals/{$id}/approve")->assertOk()->assertJsonPath('data.current_level', 2);
        Notification::assertSentTo($this->director, ApprovalNotification::class);

        // the manager can't decide level 2
        $this->postJson("/api/approvals/{$id}/approve")->assertStatus(403);

        Sanctum::actingAs($this->director);
        $this->postJson("/api/approvals/{$id}/approve", ['remarks' => 'OK'])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.levels.0.state', 'approved')
            ->assertJsonPath('data.levels.1.decided_by', 'Dee Director')
            ->assertJsonCount(3, 'data.timeline');
        Notification::assertSentTo($this->requester, ApprovalNotification::class);
    }

    public function test_reject_needs_a_reason_and_ends_the_request(): void
    {
        $id = $this->submit();
        Sanctum::actingAs($this->manager);

        $this->postJson("/api/approvals/{$id}/reject")->assertStatus(422)->assertJsonValidationErrors('remarks');
        $this->postJson("/api/approvals/{$id}/reject", ['remarks' => 'Missing info'])->assertOk()->assertJsonPath('data.status', 'rejected');
        $this->postJson("/api/approvals/{$id}/approve")->assertStatus(422);   // already decided

        // can be submitted again
        $this->submit();
    }

    public function test_one_pending_request_per_record_and_cancel(): void
    {
        $id = $this->submit();
        $this->postJson('/api/approvals/submit', ['type' => 'user', 'id' => $this->subject->id])
            ->assertStatus(422)->assertJsonPath('message', 'This record is already waiting for approval.');

        Sanctum::actingAs($this->manager);
        $this->postJson("/api/approvals/{$id}/cancel")->assertStatus(403);   // only the requester (or Administrator)

        Sanctum::actingAs($this->requester);
        $this->postJson("/api/approvals/{$id}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
    }

    public function test_record_panel_and_visibility(): void
    {
        Sanctum::actingAs($this->requester);
        $this->getJson("/api/approvals/record/user/{$this->subject->id}")->assertOk()
            ->assertJsonPath('data', null)->assertJsonPath('can_submit', true)->assertJsonPath('flow.levels', 2);

        $id = $this->submit();
        $this->getJson("/api/approvals/record/user/{$this->subject->id}")->assertJsonPath('data.id', $id)->assertJsonPath('can_submit', false);
        $this->getJson('/api/approvals?scope=requested')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/approvals?scope=all')->assertStatus(403);

        // an outsider can't open it
        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/approvals/{$id}")->assertStatus(403);
    }

    public function test_no_flow_no_submit(): void
    {
        $this->actingAsAdmin();
        $flowId = $this->getJson('/api/approval-flows')->json('data.0.id');
        $this->putJson("/api/approval-flows/{$flowId}", ['name' => 'Off', 'record_type' => 'user', 'active' => false,
            'levels' => [['approver_type' => 'user', 'approver_id' => $this->director->id]]])->assertOk();

        $this->postJson('/api/approvals/submit', ['type' => 'user', 'id' => $this->subject->id])
            ->assertStatus(422)->assertJsonPath('message', 'No active approval flow is set up for this kind of record.');
    }

    public function test_flow_setup_validation_and_permission(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/api/approval-flows', ['name' => 'X', 'record_type' => 'nope', 'levels' => []])
            ->assertStatus(422)->assertJsonValidationErrors(['record_type', 'levels']);

        $this->actingAsUserWith(['user-list']);
        $this->getJson('/api/approval-flows')->assertStatus(403);
    }
}
