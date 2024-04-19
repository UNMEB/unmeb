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

    public function __construct(Request $request)
    {
        session()->forget(['institution_id', 'course_id', 'nsin_registration_id']);

        $data = $request->all();
        $this->institutionId = $data['institution_id'] ?? null;
        $this->courseId = $data['course_id'] ?? null;
        $this->registrationId = $data['registration_id'] ?? null;

        session()->put('institution_id', $this->institutionId);
        session()->put('course_id', $this->courseId);
        session()->put('registration_id', $this->registrationId);
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $students = Student::withoutGlobalScopes()
            ->filters()
            ->select(
                'students.id',
                'institutions.institution_name',
                'courses.course_name',
                'registrations.id AS registration_id',
                'students.*',
                'student_registrations.sr_flag'
            )
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->where('registrations.id', $this->registrationId)
            ->where('courses.id', $this->courseId)
            ->where('institutions.id', $this->institutionId);


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
        dd($request->all());

        // Define validation rules
        $rules = [
            'approve_students.*' => [
                'in:0,1'
            ],
            'reject_students.*' => [
                'required_if:approve_students.*,0'
            ],
            'reject_reasons.*' => [
                'required_if:reject_students.*,1',
            ],
        ];

        $messages = [
            'reject_reasons.*.required_if' => 'The rejection reason is required when the student is rejected.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Filter out values where both approval and rejection are 0
        $studentIdsToApprove = collect($request->input('approve_students'))->filter(function ($value) {
            return $value == 1;
        })->keys();

        $studentIdsToReject = collect($request->input('reject_students'))->filter(function ($value) {
            return $value == 1;
        })->keys();

        // Handle Student Approval
        foreach ($studentIdsToApprove as $studentId) {
            $this->processRegistration($studentId, 'approve');
        }

        // Handle Student Rejection
        foreach ($studentIdsToReject as $studentId) {
            $rejectionReason = $request->input('reject_reasons')[$studentId];
            $this->processRegistration($studentId, 'reject', $rejectionReason);
        }
    }

    public function processRegistration($studentId, $action, $rejectionReason = null)
    {
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $registrationId = session('registration_id');


        $studentRegistration = StudentRegistration::where('registration_id', $registrationId)
            ->where('student_id', $studentId)->first();

        if ($studentRegistration != null) {
            if ($action == 'approve') {
                $studentRegistration->sr_flag = 1;
                $studentRegistration->save();

                // Increment the registration
                $registration = Registration::find($registrationId);

                if ($registration != null) {
                    $registration->approved += 1;
                    $registration->save();
                }
            } else {
                $studentRegistration->sr_flag = 2;
                $studentRegistration->remarks = $rejectionReason;
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
