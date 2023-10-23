<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ApproveNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

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
        return 'Approve NSIN Registrations';
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
                TD::make('actions', 'Actions')->render(
                    fn ($data) => Link::make('Verify NSIN')->route('platform.registration.nsin.approve.details', [
                        'institution_id' => $data->institution_id,
                        'course_id' => $data->course_id,
                        'nsin_registration_id' => $data->id
                    ])
                )
            ])
        ];
    }
}
