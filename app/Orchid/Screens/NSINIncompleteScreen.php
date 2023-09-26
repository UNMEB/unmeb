<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;

class NSINIncompleteScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::query()
            ->select([
                'r.*',
                'c.course_name',
                'i.institution_name',
                'y.year',
                DB::raw("(FLOOR(r.amount / 20000)) as students_to_register"),
                DB::raw("(SELECT COUNT(*) FROM students_registration_nsin WHERE nsinregistration_id = r.nsinregistration_id) as registered_students")
            ])
            ->from('nsinregistration as r')
            ->join('institutions as i', 'r.institution_id', '=', 'i.institution_id')
            ->join('courses as c', 'r.course_id', '=', 'c.course_id')
            ->join('years as y', 'r.year_id', '=', 'y.year_id')
            ->where('r.completed', 0)
            ->where('r.old', 0);

        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete NSIN Registrations';
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
