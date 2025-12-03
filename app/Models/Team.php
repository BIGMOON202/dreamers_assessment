<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    protected $fillable = ['name', 'description'];

    /**
     * Users that belong to this team (managers & associates).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('is_manager')
                    ->withTimestamps();
    }

    /**
     * Convenience helper — return the manager (first found).
     */
    public function manager()
    {
        return $this->users()->wherePivot('is_manager', true);
    }
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_team');
    }
}
