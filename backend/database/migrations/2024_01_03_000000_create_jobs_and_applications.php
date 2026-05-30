<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('location')->nullable();
            $table->boolean('remote')->default(false);
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship']);
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('skills')->nullable();
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'lead']);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'remote', 'experience_level']);
            $table->fullText(['title', 'description']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_listings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('stage', ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'])->default('applied');
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
            $table->index('stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs_listings');
    }
};
