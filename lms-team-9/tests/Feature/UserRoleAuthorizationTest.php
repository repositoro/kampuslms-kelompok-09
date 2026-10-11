<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserRoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'dosen',
            'password' => '',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'dosen',
        ]);
    }

    public function test_non_admin_cannot_change_a_users_role(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $target = User::factory()->create(['role' => 'mahasiswa']);

        Sanctum::actingAs($student);
        $this->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'admin',
            'password' => '',
        ])->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'mahasiswa',
        ]);
    }

    public function test_admin_can_manage_user_identity_fields_and_delete_unassigned_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Mahasiswa Baru',
            'email' => 'nim@example.test',
            'nim_nip' => 'MHS-123',
            'password' => 'strong-password',
            'role' => 'mahasiswa',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'nim@example.test')->firstOrFail();
        $this->put("/admin/users/{$user->id}", [
            'name' => 'Mahasiswa Diperbarui',
            'email' => $user->email,
            'nim_nip' => 'MHS-456',
            'password' => '',
            'role' => 'mahasiswa',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nim_nip' => 'MHS-456',
        ]);

        $this->delete("/admin/users/{$user->id}")
            ->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_assigned_lecturer_cannot_be_soft_deleted_or_changed_to_another_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $this->actingAs($admin)->put("/admin/users/{$lecturer->id}", [
            'name' => $lecturer->name,
            'email' => $lecturer->email,
            'role' => 'mahasiswa',
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', ['id' => $lecturer->id, 'role' => 'dosen']);

        $this->delete("/admin/users/{$lecturer->id}")
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted('users', ['id' => $lecturer->id]);
    }
}
