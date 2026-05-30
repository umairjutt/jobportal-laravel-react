<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    public const STAGES = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];

    protected $fillable = ['job_id', 'user_id', 'stage', 'cover_letter', 'resume_path'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
