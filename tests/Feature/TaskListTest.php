<?php

namespace Tests\Feature;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest is redirected to login when accessing lists.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('lists.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated user can view their lists.
     */
    public function test_authenticated_user_can_view_lists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('lists.index'));

        $response->assertStatus(200);
        $response->assertViewIs('lists.index');
    }

    /**
     * Authenticated user can create a list.
     */
    public function test_user_can_create_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'name' => 'My Test List',
            'description' => 'A test description',
        ]);

        $response->assertRedirect(route('lists.index'));

        $this->assertDatabaseHas('task_lists', [
            'name' => 'My Test List',
            'description' => 'A test description',
            'owner_id' => $user->id,
        ]);
    }

    /**
     * List creation fails without a name.
     */
    public function test_list_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('task_lists', 0);
    }

    /**
     * Owner can update their list.
     */
    public function test_owner_can_update_list(): void
    {
        $user = User::factory()->create();
        $taskList = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('lists.update', $taskList), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('lists.index'));

        $this->assertDatabaseHas('task_lists', [
            'id' => $taskList->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Non-owner cannot update another user's list (403).
     */
    public function test_non_owner_cannot_update_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $taskList = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->put(route('lists.update', $taskList), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Owner can delete their list.
     */
    public function test_owner_can_delete_list(): void
    {
        $user = User::factory()->create();
        $taskList = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('lists.destroy', $taskList));

        $response->assertRedirect(route('lists.index'));

        // Soft deleted — not in normal query
        $this->assertSoftDeleted('task_lists', [
            'id' => $taskList->id,
        ]);
    }

    /**
     * Non-owner cannot delete another user's list (403).
     */
    public function test_non_owner_cannot_delete_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $taskList = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('lists.destroy', $taskList));

        $response->assertStatus(403);
    }

    /**
     * Non-owner cannot access edit page of another user's list (403).
     */
    public function test_non_owner_cannot_access_edit_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $taskList = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('lists.edit', $taskList));

        $response->assertStatus(403);
    }

    /**
     * User's lists do not appear for other users.
     */
    public function test_user_only_sees_own_lists(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $listA = TaskList::factory()->create(['owner_id' => $userA->id, 'name' => 'List A']);
        $listB = TaskList::factory()->create(['owner_id' => $userB->id, 'name' => 'List B']);

        $response = $this->actingAs($userA)->get(route('lists.index'));

        $response->assertSee('List A');
        $response->assertDontSee('List B');
    }
}
