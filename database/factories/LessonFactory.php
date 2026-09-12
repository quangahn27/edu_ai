<?php

namespace Database\Factories;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'section_id' => Section::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'summary' => fake()->sentence(),
            'content' => '<h2>'.$title.'</h2><p>'.implode('</p><p>', [fake()->paragraph(), fake()->paragraph()]).'</p>',
            'video_url' => null,
            'duration' => fake()->numberBetween(3, 30),
            'sort_order' => 0,
            'is_preview' => false,
            'status' => LessonStatus::Published,
        ];
    }
}
