<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Response;

/**
 * @group Analytics
 *
 * Prometheus text-format metrics scrape target exposing job-board KPIs.
 * @unauthenticated
 */
class MetricsController extends Controller
{
    public function index(): Response
    {
        $jobsTotal = Job::count();
        $jobsOpen = Job::where('is_active', true)->count();
        $applicationsTotal = JobApplication::count();
        $applicationsLastMinute = JobApplication::where('created_at', '>=', now()->subMinute())->count();

        $byStage = JobApplication::query()
            ->selectRaw('stage, count(*) as c')
            ->groupBy('stage')
            ->pluck('c', 'stage');

        $lines = [];
        $lines[] = '# HELP jobportal_jobs_total Total jobs posted.';
        $lines[] = '# TYPE jobportal_jobs_total counter';
        $lines[] = "jobportal_jobs_total {$jobsTotal}";

        $lines[] = '# HELP jobportal_jobs_open Currently active job postings.';
        $lines[] = '# TYPE jobportal_jobs_open gauge';
        $lines[] = "jobportal_jobs_open {$jobsOpen}";

        $lines[] = '# HELP jobportal_applications_total Total applications submitted.';
        $lines[] = '# TYPE jobportal_applications_total counter';
        $lines[] = "jobportal_applications_total {$applicationsTotal}";

        $lines[] = '# HELP jobportal_applications_per_minute Applications in the last 60 seconds.';
        $lines[] = '# TYPE jobportal_applications_per_minute gauge';
        $lines[] = "jobportal_applications_per_minute {$applicationsLastMinute}";

        $lines[] = '# HELP jobportal_applications_by_stage Applications grouped by pipeline stage.';
        $lines[] = '# TYPE jobportal_applications_by_stage gauge';
        foreach (JobApplication::STAGES as $stage) {
            $count = (int) ($byStage[$stage] ?? 0);
            $lines[] = sprintf('jobportal_applications_by_stage{stage="%s"} %d', $stage, $count);
        }

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; version=0.0.4',
        ]);
    }
}
