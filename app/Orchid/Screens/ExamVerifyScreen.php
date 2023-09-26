<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use Orchid\Screen\Screen;

class ExamVerifyScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::distinct('r.registration_id')
        ->select(
            'r.registration_id',
            'r.receipt',
            'i.institution_name',
            'c.course_name',
            'rp.reg_start_date',
            'rp.reg_end_date',
            'r.year_of_study',
            'i.institution_id',
            'c.course_id',
            'rp.registration_period_id',
            'sf.course_fee',
            'r.amount'
        )
            ->from('registration as r')  // Explicitly specify table alias
            ->join('registration_period as rp', 'r.registration_period_id', '=', 'rp.registration_period_id')
            ->join('courses as c', 'c.course_id', '=', 'r.course_id')
            ->join('institutions as i', 'i.institution_id', '=', 'r.institution_id')
            ->join('surchage_fee as sf', function ($join) {
                $join->on('sf.course_id', '=', 'c.course_id')
                ->on('sf.surchage_id', '=', 'r.surchage_id');
            })
            ->join('surchage as s', 's.surchage_id', '=', 'sf.surchage_id')
            ->where('r.approved', 0)
            ->where('r.completed', 1)
            ->where('rp.flag', 1);


        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Verify Student Exam Registrations';
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
