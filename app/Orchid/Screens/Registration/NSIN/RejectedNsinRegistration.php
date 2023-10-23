<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class RejectedNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::query()->from('nsin_registrations AS nsin')
        ->join('institutions AS i', 'i.id', '=', 'nsin.institution_id')
        ->join('courses AS c', 'c.id', '=', 'nsin.course_id')
        ->join('years AS y', 'y.id', '=', 'nsin.year_id')
        ->join('nsin_registration_periods AS rpn', 'nsin.year_id', '=', 'rpn.year_id')
        ->leftJoinSub(
            DB::table('nsin_registrations AS nsin')
            ->join('nsin_student_registrations AS srn', 'nsin.id', '=', 'srn.nsin_registration_id')
            ->join('students AS s', 'srn.student_id', '=', 's.id')
            ->where('nsin.completed', 1)
            ->groupBy('nsin.id')
            ->select(
                'nsin.id AS nsin_registration_id',
                DB::raw('SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) AS registered_males'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) AS registered_females'),
                DB::raw('SUM(CASE WHEN srn.verify = 0 AND s.gender = "Male" THEN 1 ELSE 0 END) AS rejected_males'),
                DB::raw('SUM(CASE WHEN srn.verify = 0 AND s.gender = "Female" THEN 1 ELSE 0 END) AS rejected_females')
            ),
            'rc',
            'nsin.id',
            '=',
            'rc.nsin_registration_id'
        )
            ->select(
                'nsin.id AS nsin_registration_id',
                'i.id AS institution_id',
                'i.institution_name',
                'c.course_name',
                'c.id AS course_id',
                'nsin.MONTH',
                'nsin.year_id',
                'y.YEAR',
                'nsin.amount',
                'rc.registered_males',
                'rc.registered_females',
                'rc.rejected_males',
                'rc.rejected_females',
                DB::raw('(rc.registered_males + rc.registered_females) AS total_registered_students'),
                DB::raw('(rc.rejected_males + rc.rejected_females) AS total_rejected_students')
            )
            ->where('nsin.completed', 1)
            ->where('rpn.flag', 1);


        return [
            'registrations' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Rejected NSIN Registrations';
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
            Layout::table('registrations', [
                TD::make('nsin_registration_id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('registered_males', 'Registered Males'),
                TD::make('registered_females', 'Registered Females'),
                TD::make('total_registered_students', 'Total Registered'),
                TD::make('rejected_males', 'Rejected Males'),
                TD::make('rejected_females', 'Rejected Females'),
                TD::make('total_rejected_students', 'Total Rejected'),
                TD::make('actions', 'Actions')->render(fn (NsinRegistration $data) => Link::make('View Details')->route('platform.registration.nsin.rejected.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'nsin_registration_id' => $data->nsin_registration_id
                ]))
            ])
        ];
    }
}
