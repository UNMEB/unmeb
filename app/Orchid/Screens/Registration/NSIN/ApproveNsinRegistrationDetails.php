<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ApproveNsinRegistrationDetails extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $data = request()->all();

        $students = Student::select(
            'nsin_student_registrations.id as nsin_student_registration_id',
            'students.id AS id',
            'institutions.institution_name',
            'courses.course_name',
            'nsin_registrations.id AS nsin_registration_id',
            'students.*',
            'nsin_student_registrations.verify'
        )
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
        ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id');

        if (isset($data['nsin_registration_id'])) {
            $students->where('nsin_registrations.id', $data['nsin_registration_id']);
        }

        if (isset($data['course_id'])) {
            $students->where('courses.id', $data['course_id']);
        }

        if (isset($data['institution_id'])) {
            $students->where('institutions.id', $data['institution_id']);
        }

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
        return 'Approve/Reject Student Registration';
    }

    public function description(): ?string
    {
        $data = request()->all();
        $institutionId = $data['institution_id'] ?? null;

        if ($institutionId) {
        $institution = Institution::find($institutionId);
            if ($institution) {
                return 'Approve/Reject NSIN registrations for ' . Str::title($institution->institution_name);
            }
        }

        return null; // Return null if institution_id is null or invalid.
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
                TD::make('action', 'Actions')->render(function ($row) {
                    if ($row->verify == 0) {
                        return Button::make('Approve')
                            ->type(Color::PRIMARY)
                            ->method('approve', [
                                'id' => $row->id
                            ]);
                    } else {
                        return ModalToggle::make('Reject')
                            ->modal('rejectRegistrationModal')
                            ->modalTitle('Reject NSIN Registration')
                            ->method('reject', [
                                'id' => $row->id
                            ])
                            ->asyncParameters([
                                'student' => $row->id,
                            ]);
                    }
                })
            ]),

            Layout::modal('rejectRegistrationModal', [

                Layout::view('student_info', [
                    'name' => null
                ]),

                Layout::rows([
                    TextArea::make('reason')
                    ->title('Reason for Rejecting Student')
                    ->placeholder('Start typing...')
                    ->required()
                ])
            ])->async('asyncGetStudent')

        ];
    }

    public function asyncGetStudent(Student $student): iterable
    {
        return [
            'student' => $student,
            'name' => $student->fullName,
        ];
    }



    public function approve(Request $request, $id)
    {
        // Get the student id for this student
        $nsinStudentRegistration = Student::find($id);

        Alert::success('Student NSIN Registration approved');
    }

    public function reject(Request $request)
    {

        Alert::info('Student NSIN registration rejected.');
    }
}
