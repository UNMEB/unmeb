<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use Orchid\Screen\Screen;

class NSINVerifyScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::query()
            ->with(['institution', 'course', 'year', 'studentRegistrationNsin'])
            ->selectRaw('nsin.nsinregistration_id, COUNT(srn.student_id) AS no, i.institution_id, i.institution_name, c.course_name, c.course_id, nsin.month, nsin.year_id, y.year')
            ->from('nsinregistration as nsin')
            ->join('institutions as i', 'i.institution_id', '=', 'nsin.institution_id')
            ->join('courses as c', 'c.course_id', '=', 'nsin.course_id')
            ->join('years as y', 'y.year_id', '=', 'nsin.year_id')
            ->leftJoin('students_registration_nsin as srn', 'nsin.nsinregistration_id', '=', 'srn.nsinregistration_id')
            ->where('nsin.completed', 1)
            ->where('nsin.nsin_verify', 0)
            ->groupBy('nsin.nsinregistration_id', 'i.institution_id', 'i.institution_name', 'c.course_name', 'c.course_id', 'nsin.month', 'nsin.year_id', 'y.year');

        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Verify NSIN Student Registrations';
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
