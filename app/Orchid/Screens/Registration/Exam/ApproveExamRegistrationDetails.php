<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Student;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class ApproveExamRegistrationDetails extends Screen
{
    public $institutionId;
    public $courseId;
    public $registrationId;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $data = request()->all();
        $this->institutionId = $data['institution_id'];
        $this->courseId = $data['course_id'];
        $this->registrationId = $data['registration_id'];

        $students = Student::select(
            'students.id',
            'institutions.institution_name',
            'courses.course_name',
            'registrations.id AS registration_id',
            'students.*'
        )
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->where('registrations.id', $this->registrationId)
            ->where('courses.id', $this->courseId)
            ->where('institutions.id', $this->institutionId);


        return [
            'students' => $students->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete Exam Registrations';
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
                TD::make('old', 'Old Student'),
                TD::make('date_time', 'Registration Date'),
                TD::make('actions', 'Actions')
                    ->render(function ($data) {
                        return Group::make([
                            Button::make('Approve')
                                ->type(Color::SUCCESS)
                                ->method('approve', [
                                    'id' => $data->id
                                ]),

                            ModalToggle::make('Reject')
                                ->type(Color::DANGER)
                                ->method('reject', [
                                    'id' => $data->id
                                ])
                                ->asyncParameters([
                                    'student' => $data->id,
                                    'institution_id' => $this->institutionId,
                                    'course_id' => $this->courseId,
                                    'registration_id' => $this->registrationId
                                ])
                                ->modal('rejectStudentModal')
                                ->modalTitle('Reject Student Exam Registration')
                        ]);
                    })
            ]),

            Layout::modal('rejectStudentModal', [

                Layout::view('student_info', [
                    'name' => null,
                    'message' => 'Reject exam registration for '
                ]),

                Layout::rows([
                    TextArea::make('comment')
                        ->title('Reason for Rejection')
                        ->placeholder('Enter reason for rejection...')
                        ->help('Enter reason for rejecting the student exam registration')
                ])
            ])
                ->async('asyncGetStudent')
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
        dd($id);
    }

    public function reject(Request $request)
    {
        $registrationId = $request->input('registration_id');
        $studentId = $request->input('student');


        $studentRegistration = StudentRegistration::query()
            ->where('student_id', $studentId)
            ->where('registration_id', $registrationId)
            ->get();

        dd(collect($studentRegistration)->toJson());
    }
}
