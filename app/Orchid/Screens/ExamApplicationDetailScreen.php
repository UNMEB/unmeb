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
        $this->filters = $request->get("filter");
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $this->nsinRegistrationId = $request->get('nsin_registration_id');

        $query = Student::withoutGlobalScopes();
        $query->select([
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
            's.nsin as nsin'
        ]);

        $query->from('students As s');
        $query->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id');
        $query->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id');
        $query->leftJoin('student_registrations as sr', 'sr.student_id', '=', 's.id');
        $query->whereNotNull('sr.id');

        $query->where('s.institution_id', $institutionId);
        $query->where('nr.course_id', $courseId);
        $query->orderBy('nsr.id', 'desc');
        
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
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
            ])
        ];
    }

    public function export(Request $request)
    {
        
    }
}
