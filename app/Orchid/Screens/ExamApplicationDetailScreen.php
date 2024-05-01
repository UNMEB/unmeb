<?php

namespace App\Orchid\Screens;

use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamApplicationDetailScreen extends Screen
{
    public $nsinRegistrationId;

    public $filters = [];


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        session()->put("registration_id", $request->get('registration_id'));
        session()->put("institution_id", $request->get('institution_id'));
        session()->put("course_id", $request->get('course_id'));

        $query = Student::withoutGlobalScopes()
        ->select([
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            's.district_id',
            's.country_id',
            's.nsin as nsin',
            's.telephone',
            's.passport',
            's.passport_number',
            's.lin',
            's.email',
            'sr.trial',
            'sr.course_codes',
            'sr.number_of_papers'
        ])
        ->from('students as s')
        ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
        ->join('registrations as r', 'sr.registration_id', '=','r.id')
        ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
        ->where('rp.flag', 1)
        ->where('r.id', session('registration_id'));
        
       

        // $query->where('sr.sr_flag', 0);

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
        return 'Exam Applications';
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'View pending exam applications';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Export Applications')
            ->icon('bs.receipt')
            ->class('btn btn-success')
            ->method('export')
            ->rawClick()
        ];
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
                TD::make('id', 'ID'),
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('trial', 'Trial'),
                TD::make('course_codes', 'Course Codes'),
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
            ])
        ];
    }

    public function export(Request $request)
    {
        
    }
}
