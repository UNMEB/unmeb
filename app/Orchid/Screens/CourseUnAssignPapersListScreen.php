<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Paper;
use App\Orchid\Layouts\FormUnAssignPapers;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class CourseUnAssignPapersListScreen extends Screen
{
    /**
     * @var Course
     */
    public $course;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Course $course): iterable
    {
        $this->course = $course;

        session()->put("course_id", $course->id);
        $assignedPapers = $course->papers()->paginate();

        return [
            'course' => $course,
            'assigned_papers' => $assignedPapers,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->course->course_name;
    }

    public function description(): string|null
    {
        return 'Remove papers assigned to this course';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            FormUnAssignPapers::class,
        ];
    }

    public function submit(Request $request)
    {
        $courseId = session()->get('course_id');
        $course = Course::find($courseId);
        $data = $request->all();

        if ($request->has('unassign')) {
            $paperIds = $data['unassign'];
            foreach ($paperIds as $paperId) {
                $paper = Paper::find($paperId);
                if (!$paper) {
                    continue; // Skip to the next iteration
                }

                if ($course && $course->papers()->where('papers.id', $paperId)->exists()) {
                    $course->papers()->detach($paperId);
                    // dd('working');
                }
            }

            \RealRashid\SweetAlert\Facades\Alert::success(__('Paper has been unassigned'));

            return back();

        } else {
            $paperIds = $data['assign'];
            foreach ($paperIds as $paperId) {
                $paper = Paper::find($paperId);

                if (!$paper) {
                    // Handle case where paper is not found
                    continue; // Skip to the next iteration
                }

                // Check if paper belongs to course
                if ($course && $course->papers()->where('papers.id', $paperId)->exists()) {
                    $course->papers()->attach($paperId);
                    // dd('working');
                }
            }
        }
    }
}
