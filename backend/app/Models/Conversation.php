<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['recruiter_id', 'candidate_id', 'job_id'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function recruiter()
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function involves(int $userId): bool
    {
        return $this->recruiter_id === $userId || $this->candidate_id === $userId;
    }
}
