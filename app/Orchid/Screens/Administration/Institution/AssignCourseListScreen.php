<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Models\Course;
use App\Models\Institution;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AssignCourseListScreen extends Screen
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
        // Retrieve all course IDs assigned to the institution
        $assignedCourseIds = Institution::find($institution->id)->courses()->pluck('courses.id')->toArray();

        // Retrieve courses that are NOT assigned to the institution
        $unassignedCourses = Course::whereNotIn('id', $assignedCourseIds)->get();

        return [
            'institution' => $institution,
            'courses' => $unassignedCourses
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assign Courses to Institution ';
    }

    /**
     * The description of the screen displayed in the header.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->institution->exists() ? 'Select courses to assign to ' . $this->institution->name : 'Institution data not loaded';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Back to Institutions')->route('platform.systems.administration.institutions')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('courses', [
                TD::make('id', 'ID'),
                TD::make('name', 'Course Name'),
                TD::make('code', 'Course Code'),
                TD::make('duration', 'Course Duration'),
                TD::make('actions', 'Actions')
                    ->alignRight()
                    ->render(function ($data) {
                        return Button::make('Assign Course')->method('assign', [
                            'id' => $data->id
                        ]);
                    })
            ])
        ];
    }

    public function assign(Request $request)
    {
        // Retrieve the course ID from the request
        $courseId = $request->input('id');

        // Check if the course ID is valid
        $course = Course::find($courseId);

        if (!$course) {
            Alert::error('Course not found.');
            return redirect()->back();
        }

        // Attach the course to the institution (assuming a many-to-many relationship)
        $this->institution->courses()->attach($courseId);

        Alert::success('Course assigned successfully.');
        return redirect()->back();
    }
}
