<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function advisors()
    {
        return $this->belongsToMany(User::class, 'project_advisors');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
