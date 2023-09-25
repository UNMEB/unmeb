<?php

namespace App\Orchid\Screens;

use App\Models\Institution;
use Orchid\Screen\Screen;

class InstitutionCourseAssignScreen extends Screen
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
        $institution->load(['courses']);

        return [
            'institution' => $institution,
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
     * The description of the screen displayed in the header.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Assign courses to institution';
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
        return [];
    }
}
