<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Orchid\Layouts\FormUnAssignPrograms;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class InstitutionUnAssignCoursesListScreen extends Screen
{
    /**
     * @var Institution
     */
    public $institution;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Institution $institution): iterable
    {
        session()->put("institution_id", $institution->id);

        $assignedCourses = $institution->courses()->paginate();

        return [
            'institution' => $institution,
            'assigned_programs' => $assignedCourses,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Unassign Programs';
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
            FormUnAssignPrograms::class,
        ];
    }

    public function submit(Request $request)
    {
        $institutionId = session('institution_id');
        $institution = Institution::find($institutionId);
        $data = $request->all();

        if ($request->has('unassign')) {
            $courseIds = $data['unassign'];
            foreach ($courseIds as $courseId) {
                $course = Course::find($courseId);
                if (!$course) {
                    continue; // Skip to the next iteration
                }

                if ($institution && $institution->courses()->where('courses.id', $courseId)->exists()) {
                    $institution->courses()->detach($courseId);
                    // dd('working');
                }
            }

            \RealRashid\SweetAlert\Facades\Alert::success(__('Courses have been unassigned'));

            return back();

        }
    }
}
