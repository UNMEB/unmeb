<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Models\Course;
use App\Models\Institution;
use App\Orchid\Layouts\FormAssignPrograms;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class InstitutionAssignCoursesListScreen extends Screen
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

        session()->remove("institution_id");
        session()->put("institution_id", $institution->id);

        $assignedCourses = $institution->courses()->paginate();
        $coursesNotAssigned = Course::whereNotIn('id', $institution->courses->pluck('id'))->paginate();

        return [
            'institution' => $institution,
            'unassigned_programs' => $coursesNotAssigned,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assign Programs';
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
            FormAssignPrograms::class
        ];
    }

    /**
     * Assign a course to an institution.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request)
    {
        $institution = $this->institution;
        $institution->courses()->attach($request->get('course_id'));

        Alert::success(__('Program was assigned.'));

        return back();
    }


    public function submit(Request $request)
    {
        $institutionId = session('institution_id');
        $institution = Institution::find($institutionId);
        $data = $request->all();

        $courseIds = $data['assign'];

        foreach ($courseIds as $courseId) {
            $course = Course::find($courseId);

            if (!$course->exists) {
                continue;
            }

            if ($institution->exists && !$institution->courses()->where('courses.id', $courseId)->exists()) {
                $institution->courses()->attach($courseId);
            }
        }
    }
}
