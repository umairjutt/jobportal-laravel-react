<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'jobs_listings';

    protected $fillable = [
        'company_id', 'title', 'slug', 'description', 'location', 'remote',
        'employment_type', 'salary_min', 'salary_max', 'currency',
        'skills', 'experience_level', 'is_active', 'featured',
    ];

    protected $casts = [
        'skills' => 'array',
        'remote' => 'boolean',
        'is_active' => 'boolean',
        'featured' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
