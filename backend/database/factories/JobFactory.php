<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        $title = fake()->jobTitle();
        $min = fake()->numberBetween(40000, 90000);

        return [
            'company_id' => Company::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->city(),
            'remote' => fake()->boolean(),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contract']),
            'salary_min' => $min,
            'salary_max' => $min + fake()->numberBetween(10000, 60000),
            'currency' => 'USD',
            'skills' => fake()->randomElements(['php', 'laravel', 'react', 'aws', 'docker', 'mysql'], 3),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead']),
            'is_active' => true,
            'featured' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
