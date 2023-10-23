<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class IncompleteNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        /**
         * SELECT nsin_registrations.* FROM nsin_registrations
         * INNER JOIN institutions ON nsin_registrations.institution_id = institutions.id
         * INNER JOIN years ON nsin_registrations.year_id = years.id
         * INNER JOIN courses ON nsin_registrations.course_id = courses.id WHERE nsin_registrations.completed = 0  AND nsin_registrations.old = 0
         */

        $query = Nsinregistration::query()
            ->select([
                'r.id',
                'r.month',
                'c.course_name',
                'i.institution_name',
            'i.id as institution_id',
            'c.id as course_id',
                'y.year',
                DB::raw("(FLOOR(r.amount / 20000)) as students_to_register"),
                DB::raw("(SELECT COUNT(*) FROM nsin_student_registrations WHERE nsin_registration_id = r.id) as registered_students")
            ])
            ->from('nsin_registrations as r')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('years as y', 'r.year_id', '=', 'y.id')
            ->where('r.completed', 0)
            ->where('r.old', 0);

        return [
            'registrations' => $query
                ->orderBy('r.created_at', 'desc')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete NSIN Registration';
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
                TD::make('id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('month', 'Month'),
                TD::make('year', 'Year'),
                TD::make('students_to_register', 'Students to Register'),
                TD::make('registered_students', 'Registered Students'),
                TD::make('actions', 'Actions')->render(fn (NsinRegistration $data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
                ->route('platform.registration.nsin.incomplete.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'nsin_registration_id' => $data->id
                ]))
            ])
        ];
    }
}
