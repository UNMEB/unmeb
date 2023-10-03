<?php

namespace App\Orchid\Screens\Administration\Course;

use App\Models\Course;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CoursePaperListScreen extends Screen
{
    public $course;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Course $course): iterable
    {
        return [
            'course' => $course,
            'papers' => $course->papers()->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Course Papers for ' . $this->course->name;
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Course Papers';
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
            Layout::table('papers', [
                TD::make('id', 'ID'),
                TD::make('abbrev', 'Abbreviation'),
                TD::make('code', 'Paper Code'),
                TD::make('name', 'Paper Name'),
                TD::make('paper', 'Paper'),
                TD::make('study_period', 'Study Period'),
            ])
        ];
    }
}
