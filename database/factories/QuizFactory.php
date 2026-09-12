<?php

namespace Database\Factories;

use App\Enums\QuizStatus;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'lesson_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'time_limit' => fake()->randomElement([null, 10, 15, 30]),
            'passing_score' => 60,
            'max_attempts' => fake()->randomElement([null, 1, 3]),
            'shuffle_questions' => false,
            'status' => QuizStatus::Published,
        ];
    }
}
