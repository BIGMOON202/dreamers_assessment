<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }  

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
                    ->withPivot('is_manager')
                    ->withTimestamps();
    }

    /**
     * Return teams where this user is manager.
     */
    public function managedTeams()
    {
        return $this->teams()->wherePivot('is_manager', true);
    }

    /**
     * Helper: is this user manager of given team (or any team if null).
     */
    public function isManagerOf(Team $team = null): bool
    {
        if ($team) {
            return (bool) $this->teams()->where('teams.id', $team->id)->wherePivot('is_manager', true)->exists();
        }
        return $this->teams()->wherePivot('is_manager', true)->exists();
    }

    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function advisingProjects()
    {
        return $this->belongsToMany(Project::class, 'project_advisors'); // new pivot table for advisors
    }
}
