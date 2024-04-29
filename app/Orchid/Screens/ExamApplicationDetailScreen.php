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
            's.location',
            's.passport_number',
            's.nin',
            's.telephone',
            's.refugee_number',
            's.lin',
            's.nsin as nsin',
            'sr.trial',
            'no_of_papers',
            'course_codes'
        ])
        ->from('students As s')
            ->join('nsin_student_registrations AS nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations AS nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->join('student_registrations as sr', 'sr.student_id', '=','s.id')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('nr.institution_id', '=', session('institution_id'))
            ->where('nr.course_id', '=', session('course_id'))
            ->where('rp.flag', 1)
            ->whereNotIn('s.id', function($query) {
                $query->select('student_id')
                    ->distinct()
                    ->from('student_registrations as sr')
                    ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
                    ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
                    ->where('rp.flag', '=', 1);
            })
            ->orderBy('s.nsin', 'ASC');
        
       

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
