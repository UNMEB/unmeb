<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Orchid\Layouts\ApproveStudentsNSINsTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Orchid\Screen\Screen;
use RealRashid\SweetAlert\Facades\Alert;

class ApproveNsinRegistrationDetails extends Screen
{
    public function query(Request $request): iterable
    {
        // Clear previous values
        session()->forget(['institution_id', 'course_id', 'nsin_registration_id']);

        $data = $request->all();

        // Check and handle null values for keys
        $nsin_registration_id = $data['nsin_registration_id'] ?? null;
        $institution_id = $request->get('institution_id') ?? null;
        $course_id = $request->get('course_id') ?? null;

        session()->put("nsin_registration_id", $nsin_registration_id);
        session()->put('institution_id', $institution_id);
        session()->put('course_id', $course_id);

        $query = Student::query()
            ->select([
                's.id',
                's.surname', 
                's.firstname', 
                's.othername', 
                's.dob', 
                's.gender',
                's.country_id', 
                's.district_id', 
                's.nin', 
                's.passport_number', 
                's.refugee_number',
                ])
            ->from('students as s')
            ->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->join('years as y', 'nr.year_id', '=', 'y.id')
            ->where('nsr.verify', 0)
            ->where('nr.institution_id', $institution_id)
            ->where('c.id', $course_id)
            ->where('nr.id', $nsin_registration_id);

        $registrations = $query->orderBy('surname', 'asc')
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
        if (session()->has('institution_id')) {
            $institution = Institution::find(session('institution_id'));
            if ($institution) {
                return 'Approve/Reject NSIN registrations for ' . Str::title($institution->institution_name);
            }
        }

        return null;
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

        Alert::success('NSIN Applications Approved', "
        <table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'>
            <tbody>
                <tr>
                    <td>Number of Students Approved</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Number of Students Rejected</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        ")
        ->persistent(true)
        ->toHtml();
    }

    public function processRegistration($studentId, $action, $rejectionReason = null)
    {
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $nsinRegistrationId = session('nsin_registration_id');

        $student = Student::find($studentId);

        if (!$student) {
            return "Student not found";
        }

        $nsinStudentRegistration = NsinStudentRegistration::where("nsin_registration_id", $nsinRegistrationId)
            ->where("student_id", $studentId)
            ->first();

        if (!$nsinStudentRegistration) {
            return "Student NSIN Registration not found";
        }

        if ($action === 'approve') {
            $nsinStudentRegistration->update([
                'verify' => 1,
                'remarks' => 'Verification Complete'
            ]);
        } else if ($action === 'reject') {
            $nsinStudentRegistration->update([
                'verify' => 2,
                'remarks' => $rejectionReason
            ]);
        }
    }



}
