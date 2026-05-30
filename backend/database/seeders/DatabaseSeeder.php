<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'recruiter', 'candidate'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $admin = User::firstOrCreate(['email' => 'admin@jobs.test'], ['name' => 'Admin', 'password' => 'password']);
        $admin->syncRoles(['admin']);

        $recruiter = User::firstOrCreate(['email' => 'recruiter@jobs.test'], ['name' => 'Rachel Recruiter', 'password' => 'password']);
        $recruiter->syncRoles(['recruiter']);

        $candidate = User::firstOrCreate(['email' => 'candidate@jobs.test'], ['name' => 'Carl Candidate', 'password' => 'password']);
        $candidate->syncRoles(['candidate']);
        $candidate->candidateProfile()->updateOrCreate([], [
            'location' => 'Lahore, PK',
            'years_experience' => 4,
            'skills' => ['Laravel', 'React', 'Python'],
        ]);

        $company = Company::firstOrCreate(['slug' => 'acme-corp'], [
            'name' => 'Acme Corp',
            'website' => 'https://acme.example',
            'description' => 'We build acme widgets.',
            'owner_id' => $recruiter->id,
        ]);

        $titles = [
            'Senior Laravel Engineer',
            'Full-stack Developer (Laravel + React)',
            'Backend Engineer (PHP/Laravel)',
            'Python AI Engineer',
            'DevOps Engineer',
        ];

        foreach ($titles as $t) {
            Job::firstOrCreate(['slug' => Str::slug($t)], [
                'company_id' => $company->id,
                'title' => $t,
                'description' => "We are hiring a {$t}. Awesome team, remote-friendly.",
                'location' => 'Remote',
                'remote' => true,
                'employment_type' => 'full_time',
                'salary_min' => 60000,
                'salary_max' => 120000,
                'currency' => 'USD',
                'skills' => ['Laravel', 'React'],
                'experience_level' => 'senior',
                'is_active' => true,
                'featured' => str_contains($t, 'Senior'),
            ]);
        }
    }
}
