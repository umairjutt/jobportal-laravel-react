<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = Job::query()
            ->with('company:id,name,logo_url,slug')
            ->where('is_active', true)
            ->when($request->q, function ($q, $s) {
                $q->where(function ($qq) use ($s) {
                    $qq->where('title', 'like', "%{$s}%")
                       ->orWhere('description', 'like', "%{$s}%");
                });
            })
            ->when($request->location, fn ($q, $l) => $q->where('location', 'like', "%{$l}%"))
            ->when($request->remote === 'true', fn ($q) => $q->where('remote', true))
            ->when($request->level, fn ($q, $l) => $q->where('experience_level', $l))
            ->orderByDesc('featured')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($jobs);
    }

    public function show(string $slug): JsonResponse
    {
        $job = Job::with('company')->where('slug', $slug)->firstOrFail();
        return response()->json($job);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'remote' => ['boolean'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'salary_min' => ['nullable', 'integer'],
            'salary_max' => ['nullable', 'integer'],
            'currency' => ['nullable', 'string', 'size:3'],
            'skills' => ['nullable', 'array'],
            'experience_level' => ['required', 'in:entry,mid,senior,lead'],
        ]);

        $company = $request->user()->company;
        abort_unless($company, 422, 'Recruiter has no company.');

        $job = $company->jobs()->create([
            ...$data,
            'slug' => Str::slug($data['title']) . '-' . Str::random(6),
            'is_active' => true,
            'currency' => $data['currency'] ?? 'USD',
        ]);

        return response()->json($job, 201);
    }
}
