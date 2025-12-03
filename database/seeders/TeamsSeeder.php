<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeamsSeeder extends Seeder
{
    public function run(): void
    {
        // Create two example teams
        $teamA = Team::create(['name' => 'Alpha Team', 'description' => 'Alpha project team']);
        $teamB = Team::create(['name' => 'Beta Team',  'description' => 'Beta project team']);
        $teamC = Team::create(['name' => 'Gamma Team',  'description' => 'Gamma project team']);

        // If you have seeded users already (roles seeder + users seeder),
        // fetch some users to attach. We'll create fallback users if not present.

        $manager = User::firstWhere('email', 'manager@example.com')
                   ?? User::create([
                        'name' => 'Manager One',
                        'email' => 'manager@example.com',
                        'password' => Hash::make('password'),
                        'role_id' => \App\Models\Role::where('name','manager')->value('id') ?? 2,
                   ]);

        $associate = User::firstWhere('email', 'associate@example.com')
                   ?? User::create([
                        'name' => 'Associate One',
                        'email' => 'associate@example.com',
                        'password' => Hash::make('password'),
                        'role_id' => \App\Models\Role::where('name','associate')->value('id') ?? 3,
                   ]);

        // Attach manager (set is_manager true)
        $teamA->users()->syncWithoutDetaching([$manager->id => ['is_manager' => true]]);
        $teamA->users()->syncWithoutDetaching([$associate->id => ['is_manager' => false]]);

        // Attach to second team as example (manager also managing B)
        $teamB->users()->syncWithoutDetaching([$manager->id => ['is_manager' => true]]);
        $teamC->users()->syncWithoutDetaching([$manager->id => ['is_manager' => true]]);
    }
}
