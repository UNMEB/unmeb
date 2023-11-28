<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Exports\IncompleteExamRegistrationsExport;
use App\Jobs\GenerateCSV;
use App\Models\Account;
use App\Models\Course;
use App\Models\CoursePaper;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
use App\Models\StudentRegistration;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForExamForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\File;
use Log;

class IncompleteExamRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::filters()
            ->from('institutions as i')
            ->join('registrations as r', 'i.id', '=', 'r.institution_id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->select('i.id AS institution_id', 'i.institution_name', 'r.id as registration_id', 'c.id as course_id', 'c.course_name', 'rp.id as registration_period_id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->groupBy('i.id', 'i.institution_name', 'r.id', 'c.course_name', 'rp.id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->orderBy('r.updated_at', 'desc');

        return [
            'results' => $query->paginate(),
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
        return [
            ModalToggle::make('New Registration')
                ->modalTitle('Register Student For Exams')
                ->modal('examRegistrationModal')
                ->method('register')
                ->icon('plus')
                ->class('btn btn-sm btn-success'),

            Button::make('Export Data')
                ->method('export')
                ->icon('download')
                ->class('btn btn-primary btn-sm link-primary')
                ->rawClick(false)
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

            Layout::modal('examRegistrationModal', RegisterStudentsForExamForm::class),

            Layout::rows([
                Group::make([
                    Input::make('institution_name')
                        ->title('Filter By Institution'),

                    Input::make('course_name')
                        ->title('Filter By Program'),
                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])
                ->title('Filter Results'),

            Layout::table('results', [
                TD::make('registration_id', 'ID'),
                TD::make('institution_name', 'Institution Name'),
                TD::make('course_name', 'Course Name'),
                TD::make('register_count', 'All Registrations')
                    ->render(function ($data) {
                        $regs = StudentRegistration::query()
                            ->where('registration_id', $data->registration_id)
                            ->count();
                        return $regs;
                    }),
                TD::make('register_count', 'Pending Registrations')
                    ->render(function ($data) {
                        $regs = StudentRegistration::query()
                            ->where('registration_id', $data->registration_id)
                            ->where('sr_flag', 0)
                            ->count();
                        return $regs;
                    }),
                TD::make('actions', 'Actions')->render(function ($data) {
                    return Link::make('View Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.exam.incomplete.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'registration_id' => $data->registration_id
                        ]);
                }),

            ])
        ];
    }

    public function register(Request $request)
    {
        $examRegistrationPeriodId = $request->input('exam_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $paperIds = $request->input('paper_ids');
        $studentIds = $request->input('student_ids');
        $numberOfPapers = count($paperIds);
        $numberOfStudents = count($request->input('student_ids'));
        $yearOfStudy = $request->input('year_of_study');
        $trial = $request->input('trial');

        $costPerPaper = 50000;

        $totalCost = 0;

        // Get the institution
        $institution = Institution::find($institutionId);

        // Get the course
        $course = Course::find($courseId);


        $accountBalance = (float) $institution->account->balance;

        $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
            ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
            ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.flag', 1)
            ->first();

        // dd($normalCharge->surcharge_id);

        $bill = 0;

        // Find the registration
        $registration = Registration::where([
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'year_of_study' => $yearOfStudy,
            'registration_period_id' => $examRegistrationPeriodId,
        ])->first();

        if ($registration) {
            // Increment the bill
        } else {
            $registration = new Registration();
            $registration->institution_id = $institution->id;
            $registration->course_id = $courseId;
            $registration->amount = 0;
            $registration->year_of_study = $yearOfStudy;
            $registration->registration_period_id = $examRegistrationPeriodId;
            $registration->surcharge_id = $normalCharge->surcharge_id;
            $registration->save();
        }


        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

            // if first attempt register normally
            if ($trial == 'First') {
                $bill += $normalCharge->course_fee;
            } else if ($trial == 'Second') {
                $costToPay = ($costPerPaper + ($costPerPaper * 0.5)) * count($paperIds);
                $bill += $costToPay;
            } else {
                $costToPay = ($costPerPaper + ($costPerPaper * 1)) * count($paperIds);
                $bill += $costToPay;
            }

            // Register Student Exam Registration
            $existingRegistration = StudentRegistration::where([
                'registration_id' => $registration->id,
                'student_id' => $student->id,
                'trial' => $trial,
            ])->first();

            if($existingRegistration != null) {
                // Student already has a registration

            } else {
                $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                $existingRegistration = new StudentRegistration();
                $existingRegistration->registration_id = $registration->id;
                $existingRegistration->trial = $trial;
                $existingRegistration->student_id = $student->id;
                $existingRegistration->course_codes = $courseCodes;
                $existingRegistration->no_of_papers = count($paperIds);
                $existingRegistration->sr_flag = 0;
                $existingRegistration->save();
            }

            // Register Student Papers
            $studentCoursePapers = DB::table('course_paper') // Use the actual pivot table name
            ->where('course_id', $course->id)
            ->whereIn('paper_id', $paperIds)
            ->pluck('id');

            // dd($studentCoursePapers);

            foreach ($studentCoursePapers as $coursePaperId) {
                // Create a new StudentPaperRegistration record
                $studentPaperRegistration = new StudentPaperRegistration();
                $studentPaperRegistration->student_registration_id = $existingRegistration->id;
                $studentPaperRegistration->course_paper_id = $coursePaperId;
                $studentPaperRegistration->save();
            }

        }

        if ($bill > $accountBalance) {
            Alert::error("Account balance to low to complete transaction. Please deposit funds to your account.");
            return back();
        }

        $newBalanace = $institution->account->balance - $bill;

        // Increment this amount in registration
        $registration->amount += $newBalanace;
        $registration->save();

        $institution->account->update([
            'balance' => $newBalanace,
        ]);

        $transaction = new Transaction([
            'amount' => $bill,
            'type' => 'debit',
            'account_id' => $institution->account->id,
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
            'status' => 'approved',
            'comment' => 'Exam Registration',
        ]);

        $transaction->save();

        Alert::success('Registration successful');
    }

    public function filter(Request $request)
    {
        $institutionName = $request->input('institution_name');
        $courseName = $request->input('course_name');


        $filterParams = [];

        if (!empty($institutionName)) {
            $filterParams['filter[institution_name]'] = $institutionName;
        }

        if (!empty($courseName)) {
            $filterParams['filter[course_name]'] = $courseName;
        }

        $url = route('platform.registration.exam.incomplete', $filterParams);

        return redirect()->to($url);

    }

    public function reset(Request $request)
    {
        $url = route('platform.registration.exam.incomplete');

        return redirect()->to($url);
    }

    public function export(Request $request)
    {
        if (Storage::disk('public')->exists('public/incomplete_exam_registrations.csv')) {

            return Storage::disk('public')->download('public/incomplete_exam_registrations.csv');

        } else {
            GenerateCSV::dispatch();

            Alert::info('The file has been scheduled for download. Check back later');

            return back();
        }
    }
}
