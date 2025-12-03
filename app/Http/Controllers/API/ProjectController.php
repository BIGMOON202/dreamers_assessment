<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List all projects with teams, advisors and reviews.
     * Executives only (enforced by route middleware).
     */
    public function index()
    {
        $projects = Project::with(['teams', 'reviews', 'reviews.reviewer', 'reviews.reviewee'])->get();

        return response()->json($projects);
    }

    /**
     * Create a new project and optionally attach teams and advisors.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_ids' => 'array',
            'team_ids.*' => 'exists:teams,id',
            'advisor_ids' => 'array',
            'advisor_ids.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach teams
        if (! empty($validated['team_ids'] ?? [])) {
            $project->teams()->sync($validated['team_ids']);
        }

        // Attach advisors via project_advisors pivot
        if (! empty($validated['advisor_ids'] ?? [])) {
            $project->advisors()->sync($validated['advisor_ids']);
        }

        return response()->json($project->load(['teams', 'reviews']), 201);
    }

    /**
     * Show a single project.
     */
    public function show(Project $project)
    {
        $project->load(['teams', 'reviews', 'reviews.reviewer', 'reviews.reviewee']);

        return response()->json($project);
    }

    /**
     * Update project details, teams and advisors.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'team_ids' => 'sometimes|array',
            'team_ids.*' => 'exists:teams,id',
            'advisor_ids' => 'sometimes|array',
            'advisor_ids.*' => 'exists:users,id',
        ]);

        $project->update($validated);

        if (array_key_exists('team_ids', $validated)) {
            $project->teams()->sync($validated['team_ids'] ?? []);
        }

        if (array_key_exists('advisor_ids', $validated)) {
            $project->advisors()->sync($validated['advisor_ids'] ?? []);
        }

        return response()->json($project->load(['teams', 'reviews']));
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }
}
