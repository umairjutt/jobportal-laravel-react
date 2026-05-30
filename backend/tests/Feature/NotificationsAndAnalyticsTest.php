<?php

use App\Models\Company;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['admin', 'recruiter', 'candidate'] as $r) {
        Role::firstOrCreate(['name' => $r]);
    }

    $this->recruiter = User::factory()->create();
    $this->recruiter->assignRole('recruiter');
    $this->company = Company::factory()->create(['owner_id' => $this->recruiter->id]);

    $this->candidate = User::factory()->create();
    $this->candidate->assignRole('candidate');
});

test('applying notifies the recruiter', function () {
    Notification::fake();

    $job = Job::factory()->create(['company_id' => $this->company->id]);

    $this->actingAs($this->candidate, 'sanctum')
        ->postJson("/api/jobs/{$job->id}/apply")
        ->assertCreated();

    Notification::assertSentTo($this->recruiter, NewApplicationNotification::class);
});

test('recruiter analytics returns a funnel and conversion rates', function () {
    $job = Job::factory()->create(['company_id' => $this->company->id, 'views_count' => 100]);
    $job->applications()->create(['user_id' => $this->candidate->id, 'stage' => 'applied']);

    $this->actingAs($this->recruiter, 'sanctum')
        ->getJson('/api/analytics/dashboard')
        ->assertOk()
        ->assertJsonPath('totals.views', 100)
        ->assertJsonPath('totals.applications', 1)
        ->assertJsonStructure([
            'totals' => ['jobs', 'views', 'applications', 'hired'],
            'conversion' => ['view_to_apply', 'apply_to_hire'],
            'funnel',
            'top_jobs',
        ]);
});

test('marking a conversation read updates messages and returns count', function () {
    $conversation = Conversation::create([
        'recruiter_id' => $this->recruiter->id,
        'candidate_id' => $this->candidate->id,
    ]);

    // Candidate sends a message; recruiter then reads it.
    $conversation->messages()->create([
        'sender_id' => $this->candidate->id,
        'body' => 'Hello there',
    ]);

    $this->actingAs($this->recruiter, 'sanctum')
        ->postJson("/api/chat/conversations/{$conversation->id}/read")
        ->assertOk()
        ->assertJsonPath('read', 1);

    expect($conversation->messages()->whereNotNull('read_at')->count())->toBe(1);
});

test('viewing a job increments its view count', function () {
    $job = Job::factory()->create(['company_id' => $this->company->id, 'views_count' => 0]);

    $this->getJson("/api/jobs/{$job->slug}")->assertOk();

    expect($job->fresh()->views_count)->toBe(1);
});
