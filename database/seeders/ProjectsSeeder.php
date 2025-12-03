<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample projects and assigns them to teams for testing.
     */
    public function run(): void
    {
        // Create sample projects
        $projectA = Project::create([
            'name' => 'Project Alpha',
            'description' => 'Internal team collaboration project for testing.'
        ]);

        $projectB = Project::create([
            'name' => 'Project Beta',
            'description' => 'Cross-team project to test advisor roles.'
        ]);

        // Assign teams to projects
        // Assuming team IDs exist from TeamsSeeder
        $projectA->teams()->attach([1, 2]); // Teams 1 & 2 on Project Alpha
        $projectB->teams()->attach([2, 3]); // Teams 2 & 3 on Project Beta
    }
}
