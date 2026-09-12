<?php

namespace Database\Factories;

use App\Enums\QuizAttemptStatus;
use App\Enums\UserRole;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory()->state(['role' => UserRole::Student]),
            'started_at' => now(),
            'submitted_at' => null,
            'score' => null,
            'percentage' => null,
            'status' => QuizAttemptStatus::InProgress,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now(),
            'status' => QuizAttemptStatus::Graded,
            'score' => fake()->numberBetween(0, 10),
            'percentage' => fake()->randomFloat(2, 0, 100),
        ]);
    }
}
