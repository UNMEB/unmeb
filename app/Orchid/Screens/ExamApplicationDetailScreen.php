<?php

namespace App\Orchid\Screens;

use App\Exports\ExamApplicationExport;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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
        $registration_period_id = $request->get('registration_period_id');
        $registration_id = $request->get('registration_id');
        $institution_id = $request->get('institution_id');
        $course_id = $request->get('course_id');

        session()->put('registration_id', $registration_id);
        session()->put('institution_id', $institution_id);
        session()->put('course_id', $course_id);

        $query = RegistrationPeriod::select(
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
            'sr.no_of_papers',
            'sr.created_at',
            'sr.updated_at'
        )
            ->from('students as s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->where('rp.id', $registration_period_id)
            ->where('r.id', session('registration_id'));

        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }

        $query->distinct();

        return [
            'students' => $query->paginate()
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
            Layout::table('students', [
                TD::make('id', 'ID'),
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('trial', 'Trial'),
                TD::make('course_codes', 'Course Codes')->render(function ($data) {
                    return implode(', ', json_decode($data->course_codes));
                }),
                TD::make('no_of_papers', 'Number of Papers'),
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
            ])
        ];
    }

    public function export(Request $request)
    {
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $registrationId = session('registration_id');

        $students = Student::withoutGlobalScopes()
            ->select([
                's.id as id',
                's.surname',
                's.firstname',
                's.othername',
                's.gender',
                's.dob',
                'd.district_name as district',
                'c.nicename as country',
                's.nsin as nsin',
                's.telephone',
                'sr.trial',
                'sr.course_codes',
                'sr.no_of_papers'
            ])
            ->from('students as s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->leftJoin('countries AS c', 'c.id', '=', 's.country_id')
            ->leftJoin('districts as d', 'd.id', '=', 's.district_id')
            ->where('r.institution_id', $institutionId)
            ->where('r.course_id', $courseId)
            ->where('r.id', $registrationId)
            ->get();

        return Excel::download(new ExamApplicationExport($students), 'exam_applications.xlsx');

    }
}
