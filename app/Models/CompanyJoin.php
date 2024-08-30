<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyJoin extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'company_id','id');
    }

    public function user_details(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
