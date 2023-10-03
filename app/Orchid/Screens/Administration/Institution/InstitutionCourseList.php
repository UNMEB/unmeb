<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Models\Course;
use App\Models\Institution;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstitutionCourseList extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Institution $institution): iterable
    {

        return [
            'institution' => $institution,
            'courses' => $institution->courses
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Institution Courses';
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
            Layout::table('courses', [
                TD::make('id', 'iD'),
                TD::make('name', 'Course Name'),
                TD::make('code', 'Course Code'),
                TD::make('duration', 'Course Duration'),
                TD::make('students', 'Registered Students')->render(function (Course $row) {
                    return 0;
                })
            ])
        ];
    }
}
