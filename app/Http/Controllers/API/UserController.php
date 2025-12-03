<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users with their roles and teams.
     * Executives only (enforced by route middleware).
     */
    public function index()
    {
        $users = User::with(['role', 'teams'])->get();

        return response()->json($users);
    }

    /**
     * Create a new user and optionally attach to teams.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'team_ids' => 'array',
            'team_ids.*' => 'exists:teams,id',
            'manager_team_ids' => 'array',
            'manager_team_ids.*' => 'exists:teams,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        // Attach to teams as associate/manager
        $teamIds = $validated['team_ids'] ?? [];
        $managerTeamIds = $validated['manager_team_ids'] ?? [];

        $syncData = [];
        foreach ($teamIds as $id) {
            $syncData[$id] = ['is_manager' => in_array($id, $managerTeamIds)];
        }

        if (! empty($syncData)) {
            $user->teams()->sync($syncData);
        }

        return response()->json($user->load(['role','teams']), 201);
    }

    /**
     * Show a single user.
     */
    public function show(User $user)
    {
        $user->load(['role', 'teams']);

        return response()->json($user);
    }

    /**
     * Update user details and team assignments.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'team_ids' => 'sometimes|array',
            'team_ids.*' => 'exists:teams,id',
            'manager_team_ids' => 'sometimes|array',
            'manager_team_ids.*' => 'exists:teams,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (array_key_exists('team_ids', $validated)) {
            $teamIds = $validated['team_ids'] ?? [];
            $managerTeamIds = $validated['manager_team_ids'] ?? [];

            $syncData = [];
            foreach ($teamIds as $id) {
                $syncData[$id] = ['is_manager' => in_array($id, $managerTeamIds)];
            }

            $user->teams()->sync($syncData);
        }

        return response()->json($user->load(['role', 'teams']));
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
