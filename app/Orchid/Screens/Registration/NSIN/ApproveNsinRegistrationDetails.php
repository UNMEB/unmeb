<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Orchid\Screens\TDCheckbox;
use Illuminate\Http\Request;
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
        $this->institutionId = $data['institution_id'] ?? null;
        $this->courseId = $data['course_id'] ?? null;
        $this->nsinRegistrationId = $data['nsin_registration_id'] ?? null;
    }

    public function query(): iterable
    {
        $students = Student::select(
            'nsin_student_registrations.id as nsin_student_registration_id',
            'students.id AS id',
            'institutions.institution_name',
            'courses.course_name',
            'nsin_registrations.id AS nsin_registration_id',
            'students.*',
            'nsin_student_registrations.verify',
            'nsin_student_registrations.remarks',
            'nsin_student_registrations.nsin',
        )
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->where('institutions.id', $this->institutionId)
            ->where('courses.id', $this->courseId)
            ->where('nsin_registrations.id', $this->nsinRegistrationId);

        return [
            'students' => $students->paginate()
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

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make("Approve/Reject NSIN Applications")->modal('approveRejectModal')
                ->method('bulkAction', [
                    'nsin_registration_id' => $this->nsinRegistrationId,
                    'course_id' => $this->courseId,
                    'institution_id' => $this->institutionId
                ]),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('students', [
                TDCheckbox::make('student_ids', 'Student ID')
                    ->checkboxSet('form', 'screen-modal-form-approveRejectModal'),
                TD::make('id', 'ID'),
                TD::make('avatar', 'Passport')->render(fn (Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district_id', 'District')->render(fn (Student $student) => $student->district->district_name),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('nsin', 'NSIN'),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email'),
                TD::make('remarks', 'Remarks'),
                TD::make('Status', 'Status')->render(function ($row) {
                    return $row->verify == 1 ? 'Approved' : '';
                })
            ]),

            Layout::modal('approveRejectModal', Layout::rows([
                Select::make('action')
                    ->options([
                        'approve' => 'Approve',
                        'reject' => 'Reject'
                    ])
                    ->title('Select Action')
                    ->required(),
                TextArea::make('remarks')
                    ->title('Reason')
                    ->required()
            ]))
        ];
    }

    public function bulkAction(Request $request)
    {

        $data = $request->all();
        $studentIds = $request->get('student-ids');

        if (empty($studentIds)) {
            Alert::error("Please select students to perform the action.");
            return redirect()->back();
        }

        foreach ($studentIds as $studentId) {
            $this->processRegistration($request, $studentId, $request->get('action'));
        }

        Alert::success('Action performed successfully.');
        return redirect()->back();
    }

    public function processRegistration(Request $request, $id, $action)
    {
        $data = $request->all();

        $nsinStudentRegistration = NsinStudentRegistration::query()
            ->where('nsin_registration_id', $data['nsin_registration_id'])
            ->where('student_id', $id)
            ->latest()
            ->first();

        if ($nsinStudentRegistration != null) {
            if ($action === 'approve') {
                $nsinStudentRegistration->update([
                    'verify' => 1,
                    'remarks' => $data['remarks']
                ]);
                Alert::success('Student NSIN Registration approved');
            } elseif ($action === 'reject') {
                $nsinStudentRegistration->update([
                    'verify' => 0,
                    'remarks' => $data['remarks']
                ]);
                Alert::success('Student NSIN Registration rejected');
            }

            return redirect()->back();
        }

        Alert::error("Unable to $action student at the moment");
        return redirect()->back();
    }
}
