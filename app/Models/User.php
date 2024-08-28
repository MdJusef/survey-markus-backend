<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function company_join():HasMany
    {
        return $this->hasMany(CompanyJoin::class);
    }

    public function surveys():HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function assign_project():HasMany
    {
        return $this->hasMany(AssignProject::class,'company_id','id');
    }

    public function questions():HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function projects():HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function answer():HasOne
    {
        return $this->hasOne(Answer::class);
    }

}
