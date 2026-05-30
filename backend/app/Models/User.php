<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'avatar_url', 'headline'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'owner_id');
    }

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
