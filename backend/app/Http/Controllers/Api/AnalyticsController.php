<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Analytics
 *
 * Recruiter dashboard metrics: job views, application volume, and the
 * applied -> hired conversion funnel, scoped to the recruiter's own company.
 * @authenticated
 */
class AnalyticsController extends Controller
{
    /**
     * Recruiter dashboard
     *
     * Aggregated KPIs and a per-stage funnel for the authenticated recruiter.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        abort_unless($company, 422, 'Recruiter has no company.');

        $jobIds = Job::where('company_id', $company->id)->pluck('id');

        $totalViews = (int) Job::whereIn('id', $jobIds)->sum('views_count');
        $totalApplications = JobApplication::whereIn('job_id', $jobIds)->count();

        $stageCounts = JobApplication::query()
            ->whereIn('job_id', $jobIds)
            ->select('stage', DB::raw('count(*) as c'))
            ->groupBy('stage')
            ->pluck('c', 'stage');

        $funnel = [];
        foreach (JobApplication::STAGES as $stage) {
            $funnel[$stage] = (int) ($stageCounts[$stage] ?? 0);
        }

        $hired = $funnel['hired'] ?? 0;

        $topJobs = Job::whereIn('id', $jobIds)
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'views_count'])
            ->map(fn ($job) => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'views' => $job->views_count,
                'applications' => $job->applications_count,
                'view_to_apply_rate' => $job->views_count > 0
                    ? round($job->applications_count / $job->views_count, 4)
                    : 0.0,
            ]);

        return response()->json([
            'totals' => [
                'jobs' => $jobIds->count(),
                'views' => $totalViews,
                'applications' => $totalApplications,
                'hired' => $hired,
            ],
            'conversion' => [
                'view_to_apply' => $totalViews > 0 ? round($totalApplications / $totalViews, 4) : 0.0,
                'apply_to_hire' => $totalApplications > 0 ? round($hired / $totalApplications, 4) : 0.0,
            ],
            'funnel' => $funnel,
            'top_jobs' => $topJobs,
        ]);
    }
}
