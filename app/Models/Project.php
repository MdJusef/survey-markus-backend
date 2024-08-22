<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;

    public function surveys():HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function assign_project(): BelongsTo
    {
        return $this->belongsTo(AssignProject::class, 'project_id');
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
