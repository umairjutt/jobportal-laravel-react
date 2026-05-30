<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\NewApplicationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Applications
 *
 * Candidates apply to jobs; recruiters review and move applicants through the
 * pipeline. Status changes emit realtime notifications.
 * @authenticated
 */
class ApplicationController extends Controller
{
    public function apply(Request $request, Job $job): JsonResponse
    {
        $data = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:2000'],
            'resume_path' => ['nullable', 'string'],
        ]);

        $existing = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Already applied.'], 422);
        }

        $app = $job->applications()->create([
            'user_id' => $request->user()->id,
            'stage' => 'applied',
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_path' => $data['resume_path'] ?? optional($request->user()->candidateProfile)->resume_path,
        ]);

        $recruiter = $job->company->owner;
        $recruiter?->notify(new NewApplicationNotification($app));

        return response()->json($app, 201);
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->applications()->with('job.company')->latest()->paginate(20)
        );
    }

    public function forRecruiter(Request $request, Job $job): JsonResponse
    {
        abort_unless($job->company->owner_id === $request->user()->id, 403);
        return response()->json($job->applications()->with('candidate')->latest()->get());
    }

    public function transition(Request $request, JobApplication $application): JsonResponse
    {
        abort_unless($application->job->company->owner_id === $request->user()->id, 403);
        $data = $request->validate(['stage' => ['required', 'in:' . implode(',', JobApplication::STAGES)]]);

        $from = $application->stage;
        $application->stage = $data['stage'];
        $application->save();

        if ($from !== $application->stage) {
            $application->candidate?->notify(
                new ApplicationStatusChanged($application, $from, $application->stage)
            );
        }

        return response()->json($application);
    }
}
