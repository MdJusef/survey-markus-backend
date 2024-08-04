<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssignProject extends Model
{
    use HasFactory;

    public function projects():HasMany
    {
        return $this->hasMany(Project::class, 'id', 'project_ids');
    }

    public function getProjectIdsAttribute($value)
    {
        return json_decode($value);
    }

    public function company():BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id', 'id');
    }
}
