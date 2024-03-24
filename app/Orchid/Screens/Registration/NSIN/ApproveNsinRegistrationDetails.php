<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Orchid\Layouts\ApproveStudentsNSINsTable;
use App\Orchid\Screens\TDCheckbox;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ApproveNsinRegistrationDetails extends Screen
{

    public $institutionId;
    public $courseId;
    public $nsinRegistrationId;

    public function __construct(Request $request)
    {
        $data = $request->all();
        $this->institutionId = isset ($data['institution_id']) ? $data['institution_id'] : null;
        $this->courseId = isset ($data['course_id']) ? $data['course_id'] : null;
        $this->nsinRegistrationId = isset ($data['nsin_registration_id']) ? $data['nsin_registration_id'] : null;

        session()->put('institution_id', $this->institutionId);
        session()->put('course_id', $this->courseId);
        session()->put('nsin_registration_id', $this->nsinRegistrationId);
    }

    public function query(): iterable
    {

        $query = Student::query()
            ->select([
                'nr.id as registration_id',
                'i.institution_name',
                'i.id as institution_id', // Added institution_id
                'c.course_name',
                'c.id as course_id', // Added course_id
                'y.year as registration_year',
                'nr.month as registration_month',
                's.id as student_id', // Added student_id
                's.*', // Added student_email
                'nr.created_at as registration_date', // Added registration_date
            ])
            ->from('students AS s')
            ->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->join('years as y', 'nr.year_id', '=', 'y.id')
            ->whereNull('nsr.nsin')
            ->where('nsr.verify', 0)
            ->where('nr.institution_id', $this->institutionId)
            ->where('nr.course_id', $this->courseId)
            ->orderBy('nr.created_at', 'DESC');

        ;

        $registrations = $query
            // ->orderBy('registration_year', 'desc')
            // ->orderBy('registration_month', 'desc')
            // ->orderBy('registrations_count', 'desc')
            // ->orderBy('latest_created_at', 'desc')
            ->orderBy('surname', 'asc')
            ->paginate();


        return [
            'students' => $registrations
        ];
    }

    public function name(): ?string
    {
        return 'Student NSIN Applications';
    }

    public function description(): ?string
    {
        if ($this->institutionId) {
            $institution = Institution::find($this->institutionId);
            if ($institution) {
                return 'Approve/Reject NSIN registrations for ' . Str::title($institution->institution_name);
            }
        }

        return null;
    }

    public function commandBar(): array
    {
        return [
        ];
    }

    public function layout(): iterable
    {
        return [
            ApproveStudentsNSINsTable::class
        ];
    }

    public function submit(Request $request)
    {
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
        $institutionId = session()->get('institution_id');
        $courseId = session()->get('course_id');
        $nsinRegistrationId = session()->get('nsin_registration_id');

        $student = Student::find($studentId);
        if (!$student) {
            return "Student not found";
        }

        $nsinStudentRegistration = NsinStudentRegistration::query()
            ->where('nsin_registration_id', $nsinRegistrationId)
            ->where('student_id', $studentId)
            ->latest()
            ->first();

        if (!$nsinStudentRegistration) {
            return "Student NSIN Registration not found";
        }

        if ($action === 'approve') {
            $nsinStudentRegistration->update([
                'verify' => 1,
                'remarks' => 'Validation Complete'
            ]);
        } elseif ($action === 'reject') {
            $nsinStudentRegistration->update([
                'verify' => 2,
                'remarks' => $rejectionReason // Assign the rejection reason here
            ]);
        }
    }
}
