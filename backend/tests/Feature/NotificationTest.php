<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\Rbac;

class NotificationTest extends ApiTestCase
{
    public function test_list_read_and_delete_own_notifications(): void
    {
        $user = $this->actingAsUserWith([]);
        $user->notify(new AppNotification('First', 'Hello', '/profile'));
        $user->notify(new AppNotification('Second', '', null, 'warning'));
        User::factory()->create()->notify(new AppNotification('Not mine'));

        $list = $this->getJson('/api/notifications')->assertOk()
            ->assertJsonPath('meta.total', 2)->assertJsonPath('unread_count', 2)->json('data');
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('unread_count', 2);

        $this->postJson("/api/notifications/{$list[0]['id']}/read")->assertOk()->assertJsonPath('unread_count', 1);
        $this->getJson('/api/notifications?unread=1')->assertJsonPath('meta.total', 1);

        $this->postJson('/api/notifications/read-all')->assertOk()->assertJsonPath('unread_count', 0);
        $this->deleteJson("/api/notifications/{$list[1]['id']}")->assertOk();
        $this->getJson('/api/notifications')->assertJsonPath('meta.total', 1);
    }

    public function test_cannot_touch_someone_elses_notification(): void
    {
        $other = User::factory()->create();
        $other->notify(new AppNotification('Private'));
        $id = $other->notifications()->first()->id;
        $this->actingAsUserWith([]);

        $this->postJson("/api/notifications/{$id}/read")->assertNotFound();
        $this->deleteJson("/api/notifications/{$id}")->assertNotFound();
    }

    public function test_a_user_is_notified_of_a_new_role(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Clerk', 'guard_name' => Rbac::GUARD]);
        $target = User::factory()->create();

        $this->putJson("/api/roles/{$role->id}/users", ['user_ids' => [$target->id]])->assertOk();

        $this->assertSame(1, $target->notifications()->count());
        $this->assertSame('New role assigned', $target->notifications()->first()->data['title']);
    }
}
