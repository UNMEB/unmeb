<?php

namespace App\Orchid\Screens;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRegistrationsListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Student::withoutGlobalScopes()
        ->select(
            'nr.id as registration_id',
            'i.id as institution_id',
            'c.id as course_id',
            'i.institution_name', 
            'c.course_name',
            DB::raw('SUM(CASE WHEN nsr.verify = "1" THEN 1 ELSE 0 END) AS approved'),
            DB::raw('SUM(CASE WHEN nsr.verify = "0" THEN 1 ELSE 0 END) AS pending')
        )
        ->from('students AS s')
        ->join('nsin_student_registrations as nsr', 's.id', '=', 'nsr.student_id')
        ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=','nr.id')
        ->join('institutions as i', 'nr.institution_id', '=','i.id')
        ->join('courses as c', 'nr.course_id', '=', 'c.id')
        ->groupBy('nr.id','i.id', 'c.id')
        ->orderBy('registration_id')
        ;
        return [
            'applications' => $query->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
            Layout::table('applications', [
                TD::make('registration_id', 'Reg ID'),
                TD::make('institution_name', 'Institution Name'),
                TD::make('course_name', 'Course Name'),
                TD::make('pending', 'Pending NSINS'),
                TD::make('approved', 'Approved NSINS'),
                TD::make('actions', 'Actions')
                ->render(fn ($data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
                ->route('platform.registration.nsin.registrations.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'nsin_registration_id' => $data->registration_id
                ]))
            ])
        ];
    }

    public function details(Request $request)
    {

    }
}
