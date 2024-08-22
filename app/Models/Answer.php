<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function survey():BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
