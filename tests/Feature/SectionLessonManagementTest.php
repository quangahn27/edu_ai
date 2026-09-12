<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;

test('an instructor can add a section to their own course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->post(route('sections.store', $course), ['title' => 'Getting Started'])
        ->assertRedirect(route('courses.show', $course));

    expect($course->sections()->where('title', 'Getting Started')->exists())->toBeTrue();
});

test('an instructor cannot add a section to another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $other = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    $this->actingAs($other)
        ->post(route('sections.store', $course), ['title' => 'Sneaky Section'])
        ->assertForbidden();

    expect($course->sections()->count())->toBe(0);
});

test('sections can be reordered within a course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $first = Section::factory()->for($course)->create(['sort_order' => 0]);
    $second = Section::factory()->for($course)->create(['sort_order' => 1]);

    $this->actingAs($instructor)
        ->patch(route('sections.reorder', $course), [
            'section_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(0);
    expect($first->fresh()->sort_order)->toBe(1);
});

test('reordering rejects a section id from a different course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $otherCourse = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $foreignSection = Section::factory()->for($otherCourse)->create();

    $this->actingAs($instructor)
        ->patch(route('sections.reorder', $course), [
            'section_ids' => [$section->id, $foreignSection->id],
        ])
        ->assertSessionHasErrors('section_ids.1');
});

test('an instructor can create a lesson with sanitized content and an auto-generated slug', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('lessons.store', $section), [
            'title' => 'Variables & Types',
            'content' => '<p>Body</p><script>alert(1)</script>',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $lesson = $section->lessons()->firstOrFail();
    expect($lesson->slug)->toBe('variables-types');
    expect($lesson->content)->toBe('<p>Body</p>');
});

test('a student cannot create a lesson', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $student = User::factory()->create(['role' => UserRole::Student]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();

    $this->actingAs($student)
        ->post(route('lessons.store', $section), [
            'title' => 'Should not work',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('lessons can be reordered within a section', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $first = Lesson::factory()->for($section)->create(['sort_order' => 0]);
    $second = Lesson::factory()->for($section)->create(['sort_order' => 1]);

    $this->actingAs($instructor)
        ->patch(route('lessons.reorder', $section), [
            'lesson_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(0);
    expect($first->fresh()->sort_order)->toBe(1);
});

test('deleting a section deletes its lessons', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $section = Section::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($section)->create();

    $this->actingAs($instructor)
        ->delete(route('sections.destroy', $section))
        ->assertRedirect(route('courses.show', $course));

    expect(Lesson::find($lesson->id))->toBeNull();
});
