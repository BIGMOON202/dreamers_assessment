<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample reviews for projects and users to test API endpoints.
     */
    public function run(): void
    {
        // Example: user 2 (manager) reviews user 3 (associate)
        Review::create([
            'reviewer_id' => 2,
            'reviewee_id' => 3,
            'project_id' => 1, // Project Alpha
            'content' => 'Great collaboration, very responsive!',
            'rating' => 5
        ]);

        // Example: user 4 (advisor) reviews Project Beta
        Review::create([
            'reviewer_id' => 4,
            'reviewee_id' => null, // project-only review
            'project_id' => 2,
            'content' => 'Project planning was clear and organized.',
            'rating' => 4
        ]);
    }
}
