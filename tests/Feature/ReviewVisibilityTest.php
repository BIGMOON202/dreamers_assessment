<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Review;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    protected function getUserByRole(string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        return User::where('role_id', $role->id)->firstOrFail();
    }

    public function test_executive_can_see_all_reviews(): void
    {
        $executive = $this->getUserByRole('executive');

        $response = $this->actingAs($executive)->getJson('/api/reviews');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['id', 'content', 'rating', 'project_id', 'reviewer_id', 'reviewee_id'],
        ]);
    }

    public function test_associate_cannot_see_reviewer_identity(): void
    {
        $associate = $this->getUserByRole('associate');

        $response = $this->actingAs($associate)->getJson('/api/reviews');

        $response->assertOk();

        // reviewer identity should be hidden for non-executives
        $data = $response->json();
        if (! empty($data)) {
            $this->assertArrayNotHasKey('reviewer', $data[0]);
            $this->assertArrayNotHasKey('reviewer_id', $data[0]);
        }
    }
}


