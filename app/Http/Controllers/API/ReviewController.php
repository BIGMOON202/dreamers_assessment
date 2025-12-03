<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * List reviews based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user->role->name) {
            case 'executive':
                $reviews = Review::with(['reviewer', 'reviewee', 'project'])->get();
                break;

            case 'manager':
                // Manager sees reviews of their team members and their projects
                $teamIds = $user->teams->pluck('id');
                $projectIds = Project::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->pluck('id');
                $reviews = Review::whereIn('project_id', $projectIds)
                    ->orWhere('reviewee_id', $user->id)
                    ->with(['reviewer','reviewee','project'])
                    ->get();
                break;

            case 'associate':
                // Associates see reviews of themselves and their team's projects
                $teamIds = $user->teams->pluck('id');
                $projectIds = Project::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->pluck('id');
                $reviews = Review::where('reviewee_id', $user->id)
                    ->orWhereIn('project_id', $projectIds)
                    ->with(['reviewer','reviewee','project'])
                    ->get();
                break;

            case 'advisor':
                // Advisors see only project reviews for projects they advise on
                $projectIds = $user->advisingProjects->pluck('id'); // need belongsToMany in User model
                $reviews = Review::whereIn('project_id', $projectIds)
                    ->with(['reviewer','reviewee','project'])
                    ->get();
                break;

            default:
                return response()->json(['error'=>'Role not allowed'],403);
        }

        // Hide reviewer identity unless executive
        if ($user->role->name !== 'executive') {
            $reviews->makeHidden(['reviewer', 'reviewer_id']);
        }

        return response()->json($reviews);
    }

    /**
     * Store a new review.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'reviewee_id' => 'nullable|exists:users,id',
            'content' => 'required|string|max:500',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review = Review::create([
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $validated['reviewee_id'] ?? null,
            'project_id' => $validated['project_id'],
            'content' => $validated['content'],
            'rating' => $validated['rating'],
        ]);

        return response()->json($review, 201);
    }

    /**
     * Show a single review, respecting role-based visibility rules.
     */
    public function show(Review $review)
    {
        $user = Auth::user();

        switch ($user->role->name) {
            case 'executive':
                // executives can see any review
                break;

            case 'manager':
                // managers can see reviews of themselves, their team members & their projects
                $teamIds = $user->teams->pluck('id');
                $projectIds = Project::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->pluck('id');

                $allowed = (
                    $review->reviewee_id === $user->id ||                       // about them
                    $review->reviewer?->teams->whereIn('id', $teamIds)->isNotEmpty() || // about their team members
                    ($review->project_id && $projectIds->contains($review->project_id)) // about their projects
                );
                if (! $allowed) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                break;

            case 'associate':
                // associates can see reviews of themselves & their team’s projects
                $teamIds = $user->teams->pluck('id');
                $projectIds = Project::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->pluck('id');

                $allowed = (
                    $review->reviewee_id === $user->id ||
                    ($review->project_id && $projectIds->contains($review->project_id))
                );
                if (! $allowed) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                break;

            case 'advisor':
                // advisors can see project reviews for projects they advise
                $projectIds = $user->advisingProjects->pluck('id');
                if (! $review->project_id || ! $projectIds->contains($review->project_id)) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                break;

            default:
                return response()->json(['error' => 'Role not allowed'], 403);
        }

        // Hide reviewer name/relationship for non-executives
        if ($user->role->name !== 'executive') {
            $review->makeHidden(['reviewer', 'reviewer_id']);
        }

        // Eager load relations for consistent shape
        $review->loadMissing(['reviewer', 'reviewee', 'project']);

        return response()->json($review);
    }

    /**
     * Update a review — only by the creator.
     */
    public function update(Request $request, Review $review)
    {
        if($review->reviewer_id !== Auth::id()){
            return response()->json(['error'=>'Unauthorized'],403);
        }

        $validated = $request->validate([
            'content' => 'sometimes|string|max:500',
            'rating' => 'sometimes|integer|min:1|max:5',
        ]);

        $review->update($validated);

        return response()->json($review);
    }

    /**
     * Delete a review — only by the creator.
     */
    public function destroy(Review $review)
    {
        if($review->reviewer_id !== Auth::id() && Auth::user()->role->name !== 'executive'){
            return response()->json(['error'=>'Unauthorized'],403);
        }

        $review->delete();
        return response()->json(['message'=>'Deleted successfully']);
    }
}
