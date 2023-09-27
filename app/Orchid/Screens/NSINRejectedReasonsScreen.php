<?php

namespace App\Orchid\Screens;

use App\Models\StudentRegistrationNsin;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRejectedReasonsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $reasons = StudentRegistrationNsin::from('student_registration_nsins as srn')
        ->join('nsin_registrations as nsin', 'srn.nsin_registration_id', '=', 'nsin.id')
        ->join('institutions as i', 'i.id', '=', 'nsin.institution_id')
        ->join('courses as c', 'c.id', '=', 'nsin.course_id')
        ->select([
            'i.name as institution_name',
            'c.name as course_name',
            'srn.remarks',
            DB::raw('COUNT(srn.remarks) as no_of_remarks')
        ])
            ->where('srn.verify', '=', 0)
            ->whereNotNull('srn.remarks')
            ->where('srn.remarks', '<>', '')
            ->groupBy('i.name', 'c.name', 'srn.remarks')
            ->paginate();

        return [
            'reasons' => $reasons
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Registration Rejection Reasons';
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
            Layout::table('reasons', [
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Course'),
                TD::make('remarks', 'remark'),
                TD::make('no_of_remarks', 'Incidents'),
            ])
        ];
    }
}
