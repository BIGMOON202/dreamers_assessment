<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch role IDs
        $executiveId = Role::where('name','executive')->value('id');
        $managerId   = Role::where('name','manager')->value('id');
        $associateId = Role::where('name','associate')->value('id');
        $advisorId   = Role::where('name','advisor')->value('id');

        // Create example users
        User::create([
            'name' => 'Executive One',
            'email' => 'executive@example.com',
            'password' => Hash::make('password'),
            'role_id' => $executiveId,
        ]);

        User::create([
            'name' => 'Manager One',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role_id' => $managerId,
        ]);

        User::create([
            'name' => 'Associate One',
            'email' => 'associate@example.com',
            'password' => Hash::make('password'),
            'role_id' => $associateId,
        ]);

        User::create([
            'name' => 'Advisor One',
            'email' => 'advisor@example.com',
            'password' => Hash::make('password'),
            'role_id' => $advisorId,
        ]);
    }
}
