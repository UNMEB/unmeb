<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Models\Course;
use App\Models\Institution;
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
        $assignedCourses = $institution->courses()->paginate();
        $coursesNotAssigned = Course::whereNotIn('id', $institution->courses->pluck('id'))->paginate();

        return [
            'institution' => $institution,
            'assigned_courses' => $assignedCourses,
            'courses_not_assigned' => $coursesNotAssigned,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assign Courses';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
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
            Layout::tabs(['Assigned Programs' =>   Layout::table('assigned_courses', [
                    TD::make('id', 'ID'),
                    TD::make('course_code', 'Program Code'),
                    TD::make('course_name', 'Program Name'),
                    TD::make('duration', 'Duration'),
                    TD::make('created_at', 'Created At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('updated_at', 'Updated At')
                    ->usingComponent(DateTimeSplit::class),
                ]),
                'Assign Programs' =>  Layout::table('courses_not_assigned', [
                    TD::make('id', 'ID'),
                    TD::make('course_code', 'Program Code'),
                    TD::make('course_name', 'Program Name'),
                    TD::make('duration', 'Duration'),
                    TD::make('created_at', 'Created At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('updated_at', 'Updated At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('action', 'Action')
                    ->align(TD::ALIGN_CENTER)
                        ->render(function (Course $course) {
                            return Button::make('Assign')
                                ->method('assign', ['course_id' => $course->id]);
                        })
                ]),
            ]),
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
}
