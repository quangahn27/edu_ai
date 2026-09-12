<?php

namespace App\Http\Controllers;

use App\Http\Requests\Section\ReorderSectionsRequest;
use App\Http\Requests\Section\StoreSectionRequest;
use App\Http\Requests\Section\UpdateSectionRequest;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    public function store(StoreSectionRequest $request, Course $course): RedirectResponse
    {
        $course->sections()->create([
            ...$request->validated(),
            'sort_order' => $course->sections()->max('sort_order') + 1,
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Section added.');
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return redirect()->route('courses.show', $section->course_id)
            ->with('success', 'Section updated.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        Gate::authorize('delete', $section);

        $courseId = $section->course_id;
        $section->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Section deleted.');
    }

    public function reorder(ReorderSectionsRequest $request, Course $course): RedirectResponse
    {
        foreach ($request->validated('section_ids') as $index => $sectionId) {
            Section::whereKey($sectionId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Sections reordered.');
    }
}
