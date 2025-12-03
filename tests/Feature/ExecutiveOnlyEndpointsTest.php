<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveOnlyEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles so we can attach proper role names
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function createUserWithRole(string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_non_executive_cannot_access_user_management(): void
    {
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($manager)->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_executive_can_access_user_management(): void
    {
        $executive = $this->createUserWithRole('executive');

        $response = $this->actingAs($executive)->getJson('/api/users');

        $response->assertOk();
    }

    public function test_non_executive_cannot_access_project_management(): void
    {
        $associate = $this->createUserWithRole('associate');

        $response = $this->actingAs($associate)->getJson('/api/projects');

        $response->assertStatus(403);
    }

    public function test_executive_can_access_project_management(): void
    {
        $executive = $this->createUserWithRole('executive');

        $response = $this->actingAs($executive)->getJson('/api/projects');

        $response->assertOk();
    }
}


