<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'location', 'years_experience', 'skills', 'resume_path'];

    protected $casts = ['skills' => 'array', 'years_experience' => 'integer'];
}
