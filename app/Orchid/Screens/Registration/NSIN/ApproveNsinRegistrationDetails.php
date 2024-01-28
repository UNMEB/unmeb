<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Log;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
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

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
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

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
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
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district_id', 'District')->render(fn(Student $student) => $student->district->district_name),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('nsin', 'NSIN'),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email'),
                TD::make('remarks', 'Remarks'),
                TD::make('action', 'Actions')->render(function ($row) {
                    return Group::make([
                        Button::make('Approve')
                            ->type(Color::SUCCESS)
                            ->method('approve', [
                                'id' => $row->id,
                                'nsin_registration_id' => $this->nsinRegistrationId,
                                'course_id' => $this->courseId,
                                'institution_id' => $this->institutionId
                            ])->canSee($row->verify == 0),

                        ModalToggle::make('Reject')
                            ->modal('rejectRegistrationModal')
                            ->modalTitle('Reject NSIN Registration')
                            ->method('reject', [
                                'id' => $row->id,
                                'nsin_registration_id' => $this->nsinRegistrationId,
                                'course_id' => $this->courseId,
                                'institution_id' => $this->institutionId
                            ])
                            ->type(Color::DANGER),
                    ]);
                })
            ]),

            Layout::modal('rejectRegistrationModal', [

                Layout::view('student_info', [
                    'name' => null,
                    'message' => 'Reject NSIN registration for '
                ]),

                Layout::rows([

                    TextArea::make('remarks')
                        ->title('Reason for Rejecting Student')
                        ->placeholder('Enter reason for rejection...')
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
        return $this->processRegistration($request, $id, 'approve');
    }

    public function reject(Request $request, $id)
    {
        return $this->processRegistration($request, $id, 'reject');
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
                $nsinStudentRegistration->update(['verify' => 1]);
                Alert::success('Student NSIN Registration approved');
            } elseif ($action === 'reject') {
                $nsinStudentRegistration->update([
                    'verify' => 0,
                    'remarks' => $data['remarks']
                ]);
                $nsinStudentRegistration->save();
                Alert::success('Student NSIN Registration rejected');
            }

            return redirect()->back();
        }

        Alert::error("Unable to $action student at the moment");
        return redirect()->back();
    }

}
