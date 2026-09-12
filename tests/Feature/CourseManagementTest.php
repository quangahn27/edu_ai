<?php

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guests are redirected to login', function () {
    $this->get(route('courses.index'))->assertRedirect(route('login'));
});

test('students cannot access the course management area', function () {
    $student = User::factory()->create(['role' => UserRole::Student]);

    $this->actingAs($student)
        ->get(route('courses.index'))
        ->assertForbidden();
});

test('an instructor can create a course with an auto-generated slug', function () {
    Storage::fake('public');
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);

    $response = $this->actingAs($instructor)->post(route('courses.store'), [
        'title' => 'Introduction to Laravel',
        'short_description' => 'Learn the basics.',
        'description' => '<p>Course overview</p>',
        'level' => 'beginner',
        'status' => 'draft',
        'thumbnail' => UploadedFile::fake()->image('cover.jpg'),
    ]);

    $course = Course::firstOrFail();

    $response->assertRedirect(route('courses.show', $course));
    expect($course->title)->toBe('Introduction to Laravel');
    expect($course->slug)->toBe('introduction-to-laravel');
    expect($course->instructor_id)->toBe($instructor->id);
    Storage::disk('public')->assertExists($course->thumbnail);
});

test('creating a course sanitizes the description HTML', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);

    $this->actingAs($instructor)->post(route('courses.store'), [
        'title' => 'Security 101',
        'description' => '<p>Hello</p><script>alert(1)</script>',
        'level' => 'beginner',
        'status' => 'draft',
    ]);

    $course = Course::firstOrFail();

    expect($course->description)->toBe('<p>Hello</p>');
});

test('an instructor cannot update another instructor course', function () {
    $owner = User::factory()->create(['role' => UserRole::Instructor]);
    $other = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($owner, 'instructor')->create();

    $this->actingAs($other)
        ->put(route('courses.update', $course), [
            'title' => 'Hijacked title',
            'level' => 'beginner',
            'status' => 'draft',
        ])
        ->assertForbidden();

    expect($course->fresh()->title)->not->toBe('Hijacked title');
});

test('an instructor can update their own course and regenerates the slug on title change', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $course = Course::factory()->for($instructor, 'instructor')->create(['title' => 'Old Title']);

    $this->actingAs($instructor)
        ->put(route('courses.update', $course), [
            'title' => 'New Title',
            'level' => $course->level->value,
            'status' => $course->status->value,
        ])
        ->assertRedirect(route('courses.edit', $course));

    $course->refresh();
    expect($course->title)->toBe('New Title');
    expect($course->slug)->toBe('new-title');
});

test('an admin can delete any course', function () {
    $instructor = User::factory()->create(['role' => UserRole::Instructor]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($admin)
        ->delete(route('courses.destroy', $course))
        ->assertRedirect(route('courses.index'));

    expect(Course::find($course->id))->toBeNull();
});
