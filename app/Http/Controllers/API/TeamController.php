<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * List all teams with users and projects.
     * Executives only (enforced by route middleware).
     */
    public function index()
    {
        $teams = Team::with(['users', 'projects'])->get();

        return response()->json($teams);
    }

    /**
     * Create a new team and optionally attach users (managers/associates).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'manager_ids' => 'array',
            'manager_ids.*' => 'exists:users,id',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $userIds = $validated['user_ids'] ?? [];
        $managerIds = $validated['manager_ids'] ?? [];

        $syncData = [];
        foreach ($userIds as $id) {
            $syncData[$id] = ['is_manager' => in_array($id, $managerIds)];
        }

        if (! empty($syncData)) {
            $team->users()->sync($syncData);
        }

        return response()->json($team->load(['users', 'projects']), 201);
    }

    /**
     * Show a single team.
     */
    public function show(Team $team)
    {
        $team->load(['users', 'projects']);

        return response()->json($team);
    }

    /**
     * Update team details and user assignments.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'user_ids' => 'sometimes|array',
            'user_ids.*' => 'exists:users,id',
            'manager_ids' => 'sometimes|array',
            'manager_ids.*' => 'exists:users,id',
        ]);

        $team->update($validated);

        if (array_key_exists('user_ids', $validated)) {
            $userIds = $validated['user_ids'] ?? [];
            $managerIds = $validated['manager_ids'] ?? [];

            $syncData = [];
            foreach ($userIds as $id) {
                $syncData[$id] = ['is_manager' => in_array($id, $managerIds)];
            }

            $team->users()->sync($syncData);
        }

        return response()->json($team->load(['users', 'projects']));
    }

    /**
     * Delete a team.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => 'Team deleted']);
    }
}

