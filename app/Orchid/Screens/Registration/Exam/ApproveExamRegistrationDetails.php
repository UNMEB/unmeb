<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\District;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Orchid\Layouts\ApproveStudentsForExamTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
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
    public function query(Request $request): iterable
    {
        session()->remove('institution_id');
        session()->remove('course_id');
        session()->remove('registration_id');
        session()->remove('trial');

        session()->put('institution_id', $request->get('institution_id'));
        session()->put('course_id', $request->get('course_id'));
        session()->put('registration_id', $request->get('registration_id'));
        session()->put('trial', $request->get('trial'));

        $students = Student::withoutGlobalScopes()
            ->filters()
            ->select(
                'students.id',
                'institutions.institution_name',
                'courses.course_name',
                'registrations.id AS registration_id',
                'students.*',
                'student_registrations.sr_flag',
            )
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->where('registrations.id', session('registration_id'))
            ->where('courses.id', session('course_id'))
            ->where('institutions.id', session('institution_id'))
            ->where('student_registrations.sr_flag', 0);

        return [
            'students' => $students->paginate(100),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Approve/Reject Student Exam Registration';
    }

    public function description(): ?string
    {
        if ($this->institutionId) {
            $institution = Institution::find($this->institutionId);
            if ($institution) {
                return 'Approve/Reject Exam registrations for ' . Str::title($institution->institution_name);
            }
        }

        return null;
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

            Layout::rows([

                Group::make([

                    Input::make('name')
                        ->title('Search by Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('District of origin'),

                    Select::make('gender')
                        ->title('Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter', [
                            'institution_id' => $this->institutionId,
                            'course_id' => $this->courseId,
                            'registration_id' => $this->registrationId,
                        ]),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title("Filter Students"),

            ApproveStudentsForExamTable::class,

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


    public function submit(Request $request)
    {
        // Filter out values where both approval and rejection are 0
        $studentIdsToApprove = $request->input('approve-students');

        if ($studentIdsToApprove != null) {
            // Handle Student Approval
            foreach ($studentIdsToApprove as $studentId) {
                $data = (object) [
                    'institution_id' => $request->session()->get('institution_id'),
                    'course_id' => $request->session()->get('course_id'),
                    'registration_id' => $request->session()->get('registration_id'),
                    'action' => 'approve',
                    'student_id' => $studentId,
                    'trial' => $request->session()->get('trial')
                ];

                $this->processRegistration($data);
            }
        }

        $studentIdsToReject = $request->input('reject-students');

        if ($studentIdsToReject != null) {
            // Handle Student Rejection
            foreach ($studentIdsToReject as $studentId) {
                $rejectionReason = $request->input('reject_reasons')[$studentId];

                $data = (object) [
                    'institution_id' => $request->session()->get('institution_id'),
                    'course_id' => $request->session()->get('course_id'),
                    'registration_id' => $request->session()->get('registration_id'),
                    'action' => 'reject',
                    'student_id' => $studentId,
                    'reason' => $rejectionReason,
                    'trial' => $request->session()->get('trial')
                ];

                $this->processRegistration($data);
            }
        }

        \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', 'Students successfully approved for Exams');
    }

    public function processRegistration($data)
    {
        $studentRegistration = StudentRegistration::query()
            ->where('registration_id', $data->registration_id)
            ->where('student_id', $data->student_id)
            ->where('trial', $data->trial)
            ->first();

        if ($studentRegistration != null) {
            if ($data->action == 'approve') {
                $studentRegistration->sr_flag = 1;
                $studentRegistration->save();
            } else {
                $studentRegistration->sr_flag = 2;
                $studentRegistration->remarks = $data->reason;
                $studentRegistration->save();
            }
        }
    }


    public function filter(Request $request)
    {

        // Get existing query parameters
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $registrationId = $request->input('registration_id');

        // Get new filter parameters
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        // Prepare the filters array with only new filter parameters
        $filters = [];

        if (!empty($name)) {
            $filters['filter[name]'] = $name;
        }
        if (!empty($gender)) {
            $filters['filter[gender]'] = $gender;
        }
        if (!empty($district)) {
            $filters['filter[district_id]'] = $district;
        }

        // Combine existing query parameters with new filters
        $queryParams = array_merge([
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'registration_id' => $registrationId
        ], $filters);

        // Redirect to the same route with updated query parameters
        $url = route('platform.registration.exam.approve.details', $queryParams);

        return Redirect::to($url);
    }

    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.exam.approve.details');
    }
}
