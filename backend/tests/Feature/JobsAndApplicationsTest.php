<?php

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['admin', 'recruiter', 'candidate'] as $r) Role::firstOrCreate(['name' => $r]);

    $this->recruiter = User::create(['name' => 'R', 'email' => 'r@r.com', 'password' => 'password']);
    $this->recruiter->assignRole('recruiter');
    $this->company = Company::create(['name' => 'C', 'slug' => 'c', 'owner_id' => $this->recruiter->id]);

    $this->candidate = User::create(['name' => 'C', 'email' => 'c@c.com', 'password' => 'password']);
    $this->candidate->assignRole('candidate');
});

test('public can list jobs', function () {
    Job::create([
        'company_id' => $this->company->id, 'title' => 'Dev', 'slug' => 'dev',
        'description' => 'X', 'employment_type' => 'full_time', 'experience_level' => 'mid',
        'is_active' => true,
    ]);

    $this->getJson('/api/jobs')->assertOk()->assertJsonStructure(['data', 'current_page']);
});

test('candidate can apply once only', function () {
    $job = Job::create([
        'company_id' => $this->company->id, 'title' => 'Dev', 'slug' => 'dev',
        'description' => 'X', 'employment_type' => 'full_time', 'experience_level' => 'mid', 'is_active' => true,
    ]);

    $this->actingAs($this->candidate, 'sanctum');
    $this->postJson("/api/jobs/{$job->id}/apply")->assertCreated();
    $this->postJson("/api/jobs/{$job->id}/apply")->assertUnprocessable();
});

test('recruiter can post a job', function () {
    $this->actingAs($this->recruiter, 'sanctum')
        ->postJson('/api/jobs', [
            'title' => 'Senior Engineer',
            'description' => 'Build things',
            'employment_type' => 'full_time',
            'experience_level' => 'senior',
        ])->assertCreated();
});
