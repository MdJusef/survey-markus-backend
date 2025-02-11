<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousSurveyAnswer extends Model
{
    use HasFactory;

    public function question():BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    //survey
    public function survey():BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
