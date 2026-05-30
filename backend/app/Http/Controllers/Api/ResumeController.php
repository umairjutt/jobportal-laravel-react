<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $path = 'resumes/' . $request->user()->id . '/' . Str::random(20) . '.' . $request->file('file')->getClientOriginalExtension();

        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($request->file('file')));

        $profile = $request->user()->candidateProfile()->firstOrCreate(['user_id' => $request->user()->id]);
        $profile->resume_path = $path;
        $profile->save();

        return response()->json(['path' => $path]);
    }
}
