<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Exports\IncompleteNsinRegistrationsExport;
use App\Models\District;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Maatwebsite\Excel\Excel as ExcelExcel;

class IncompleteNsinRegistrationDetails extends Screen
{
    public $registration;
    public $institutionId;
    public $courseId;
    public $nsinRegistrationId;
    public function __construct(Request $request)
    {
        $this->institutionId = request()->get('institution_id');
        $this->courseId = request()->get('course_id');
        $this->nsinRegistrationId = request()->get('nsin_registration_id');
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $students = Student::select(
            'students.id',
            'institutions.institution_name',
            'courses.course_name',
            'nsin_registrations.id AS nsin_registration_id',
            'students.*'
        )
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->where('institutions.id', $this->institutionId)
            ->where('nsin_registrations.id', $this->nsinRegistrationId)
            ->where('courses.id', $this->courseId);

        return [
            'students' => $students->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Export Data')
            ->icon('bs.download')
            ->method('download', [
                'institution_id' => $this->institutionId,
                'course_id' => $this->courseId,
                'nsin_registration_id' => $this->nsinRegistrationId
            ])
            ->rawClick()
            ->class('btn btn-primary')
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

            Layout::rows([
                Group::make([
                    
                    Input::make('name')
                    ->title('Filter By Name'),

                    Select::make('district_id')
                    ->title('Filter By District')
                    ->fromModel(District::class, 'district_name')
                    ->empty('Non Selected'),

                    Select::make('gender')
                    ->title('Filter By Gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female'
                    ])
                    ->empty('Non Selected')
                ])
            ]),

            Layout::table('students', [

                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn (Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district_id', 'District')->render(fn (Student $student) => $student->district->district_name),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('NSIN', 'NSIN'),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email'),
                TD::make('old', 'Old Student'),
                TD::make('date_time', 'Registration Date'),
            ]),
        ];
    }

    public function download(Request $request)
    {
        $institutionId = request()->get('institution_id');
        $courseId = request()->get('course_id');
        $nsinRegistrationId = request()->get('nsin_registration_id');

        return Excel::download(new IncompleteNsinRegistrationsExport(
            $institutionId,
            $courseId,
            $nsinRegistrationId
        ), 'incomplete_nsin_registrations.csv', ExcelExcel::CSV);
    }
}
