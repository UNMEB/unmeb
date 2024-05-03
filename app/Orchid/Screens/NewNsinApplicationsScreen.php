<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\LogbookFee;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForNSINForm;
use App\Orchid\Layouts\RegisterStudentsForNSINTable;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NewNsinApplicationsScreen extends Screen
{
    public $institutionId;
    public $courseId;
    public $nsinRegistrationPeriodId;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        session()->put('institution_id', $request->get('institution_id'));
        session()->put('course_id', $request->get('course_id'));
        session()->put('nsin_registration_period_id', $request->get('nsin_registration_period_id'));

        $currentPeriod = NsinRegistrationPeriod::query()->where('id', session('nsin_registration_period_id'))->first();

        $query = Student::withoutGlobalScopes()
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
                's.nsin'
            ])
            ->from('students as s')
            ->where('s.institution_id', session('institution_id'));

        $courseCodes = Course::where('course_code', 'LIKE', 'C%')->pluck('course_code');
        $selectedCourseCode = Course::whereId(session('course_id'))->value('course_code');

        if ($courseCodes->contains($selectedCourseCode)) {
            $query->whereNull('s.nsin');
        }

        // TODO Find a solution for filtering Diploma Students

        $query->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('nsin_student_registrations as nsr')
                ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
                ->join('institutions as i', 'i.id', '=', 'nr.institution_id')
                ->join('nsin_registration_periods as nrp', function ($join) {
                    $join->on('nr.year_id', '=', 'nrp.year_id')
                        ->on('nr.month', '=', 'nrp.month');
                })
                ->whereColumn('nsr.student_id', 's.id')
                ->where('i.id', session('institution_id'))
                ->where('nrp.flag', 1);
        })
            ->orderBy('s.nsin', 'asc');

        return [
            'students' => $query->paginate(10)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Apply Students For NSINs';
    }

    public function description(): ?string
    {
        return 'Select students for NSIN Application from the table below and submit';
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
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->chunk(20),

                    Input::make('name')
                        ->title('Filter By Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('Filter By District of origin'),

                    Select::make('gender')
                        ->title('Filter By Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title("Filter Students"),
            RegisterStudentsForNSINTable::class
        ];
    }

    public function submit(Request $request)
    {
        DB::beginTransaction();

        try {

            $feesTotal = 0;

            $nrpID = session('nsin_registration_period_id');
            $institutionId = session('institution_id');
            $courseId = session('course_id');

            $settings = \Config::get('settings');
            $nsinRegistrationFee = $settings['fees.nsin_registration'];
            $researchGuidelineFee = $settings['fees.research_fee'];

            if ($nsinRegistrationFee == 0 || is_null($nsinRegistrationFee)) {
                throw new Exception('NSIN Student registration fee not yet set. Please contact support at UNMEB');
            }

            if ($researchGuidelineFee == 0 || is_null($researchGuidelineFee)) {
                throw new Exception('Research Guideline fees not yet set. Please contact support at UNMEB');
            }

            $logbookFee = LogbookFee::firstWhere('course_id', $courseId);

            if (!$logbookFee) {
                throw new Exception("Unable to register students at the moment. The logbook fees for this course are not yet set. Please contact UNMEB Support", 1);
            }

            // Check if the course is a diploma course
            $courseCode = Course::where('id', $courseId)->value('course_code');
            $isDiplomaCourse = Str::startsWith($courseCode, ['A', 'D']);

            $students = collect($request->get('students'))->keys();

            if ($students->count() == 0) {
                throw new Exception('Unable to submit data. You have not selected any students to register');
            }

            $studentIds = collect($request->get('students'))->values();

            $sortedStudentIds = Student::whereIn('id', $studentIds)->orderBy('surname')->pluck('id')->toArray();

            
    

            $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);
            $yearId = $nsinRegistrationPeriod->year_id;
            $month = $nsinRegistrationPeriod->month;

            $institution = Institution::findOrFail($institutionId);
            
            // Total Up the fees
            foreach ($sortedStudentIds as $key => $studentId) {
                $feesTotal += $nsinRegistrationFee + $logbookFee->course_fee + ($isDiplomaCourse ? $researchGuidelineFee : 0);

                $student = Student::where('id', $studentId)->update(['nsin' => null]);
            }

            if ($feesTotal > $institution->account->balance) {
                throw new Exception('Account balance too low to complete this transaction. Please top up to continue');
            }


            $nsinRegistration = NsinRegistration::where([
                'institution_id' => $institution->id,
                'course_id' => $courseId,
                'month' => $month,
                'year_id' => $yearId,
            ])->first();

            if (!$nsinRegistration) {
                $nsinRegistration = new NsinRegistration();
                $nsinRegistration->institution_id = $institution->id;
                $nsinRegistration->course_id = $courseId;
                $nsinRegistration->month = $month;
                $nsinRegistration->year_id = $yearId;
                $nsinRegistration->completed = 0;
                $nsinRegistration->approved = 0;
                $nsinRegistration->books = 0;
                $nsinRegistration->nsin = 0;
                $nsinRegistration->nsin_verify = 0;
                $nsinRegistration->old = 0;
                $nsinRegistration->save();
            }

            $institutionCode = Institution::where('id', $nsinRegistration->institution_id)->value('code');
            $courseCode = Course::where('id', $nsinRegistration->course_id)->value('course_code');

            $nsinMonth = Str::upper(Str::limit($nsinRegistration->month, 3, ''));
            $nsinYear = Str::substr($nsinRegistration->year->year, 2); // Accessing year from the eager loaded relationship

            // Generate the NSIN pattern
            $nsinPattern = "{$nsinMonth}{$nsinYear}/{$institutionCode}/{$courseCode}";

            $matchingNSINs = NsinStudentRegistration::withoutGlobalScopes()
                ->where('nsin', 'LIKE', "$nsinPattern%")
                ->select('nsin')
                ->unionAll(function ($query) use ($nsinPattern) {
                    $query->select('nsin')
                        ->from('students')
                        ->where('nsin', 'LIKE', "$nsinPattern%");
                })
                ->orderBy('nsin', 'DESC')
                ->first();

            if (!$matchingNSINs) {
                $studentCode = "001";
            } else {
                $studentCode = $matchingNSINs->student_code;
            }

            
            foreach ($sortedStudentIds as $key => $studentId) {

                $studentFees = $nsinRegistrationFee + $logbookFee->course_fee + ($isDiplomaCourse ? $researchGuidelineFee : 0);

                // Generate unique student code
                $studentCode = $this->generateUniqueStudentCode($nsinPattern);

                // Find if this student code already exists
                $existingStudentRegistration = NsinStudentRegistration::withoutGlobalScopes()
                    ->where('student_code', $studentCode)
                    ->where('student_id', $studentId)
                    ->where('nsin_registration_id', $nsinRegistration->id)
                    ->first();

                if (!$existingStudentRegistration) {
                    // Create new NSIN Student Registration
                    $nsinStudentRegistration = new NsinStudentRegistration();
                    $nsinStudentRegistration->nsin_registration_id = $nsinRegistration->id;
                    $nsinStudentRegistration->student_id = $studentId;
                    $nsinStudentRegistration->nsin = $nsinPattern . '/' . $studentCode;
                    $nsinStudentRegistration->student_code = $studentCode;
                    $nsinStudentRegistration->verify = 0;
                    $nsinStudentRegistration->save();

                    // Create Transaction for NSIN registration fee for this student
                    $nsinTransaction = new Transaction([
                        'amount' => $nsinRegistrationFee,
                        'type' => 'debit',
                        'account_id' => $institution->account->id,
                        'institution_id' => $institution->id,
                        'initiated_by' => auth()->user()->id,
                        'status' => 'approved',
                        'comment' => 'NSIN Registration Fee for Student ID: ' . $studentId,
                    ]);
                    $nsinTransaction->save();

                    // Create Transaction for logbook fee for this student
                    $logbookTransaction = new Transaction([
                        'amount' => $logbookFee->course_fee,
                        'type' => 'debit',
                        'account_id' => $institution->account->id,
                        'institution_id' => $institution->id,
                        'initiated_by' => auth()->user()->id,
                        'status' => 'approved',
                        'comment' => 'Logbook Fee for Student ID: ' . $studentId,
                    ]);
                    $logbookTransaction->save();

                    if ($isDiplomaCourse) {
                        // Create Transaction for research guideline fee for this student
                        $researchTransaction = new Transaction([
                            'amount' => $researchGuidelineFee,
                            'type' => 'debit',
                            'account_id' => $institution->account->id,
                            'institution_id' => $institution->id,
                            'initiated_by' => auth()->user()->id,
                            'status' => 'approved',
                            'comment' => 'Research Guideline Fee for Student ID: ' . $studentId,
                        ]);
    
                        $researchTransaction->save();
                    }
                }


                $institution->account->update([
                    'balance' => $institution->account->balace - $feesTotal,
                ]);

                $numberOfStudents = count($studentIds);
                $nsinTotal = $nsinRegistrationFee * $numberOfStudents;
                $logbookTotal = $logbookFee->course_fee * $numberOfStudents;
                $researchGuidelineTotal = $isDiplomaCourse ? ($researchGuidelineFee * $numberOfStudents) : 0;
                $totalDeduction = $nsinTotal + $logbookTotal + $researchGuidelineTotal;
                $remainingBalance = $institution->account->balance;
    
                $amountForNSIN = 'Ush ' . number_format($nsinTotal);
                $amountForLogbook = 'Ush ' . number_format($logbookTotal);
                $amountForResearch = 'Ush ' . number_format($researchGuidelineTotal);
                $totalDeductionFormatted = 'Ush ' . number_format($totalDeduction);
                $remainingBalanceFormatted = 'Ush ' . number_format($remainingBalance);
    
                \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', "<table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'><tbody><tr><th style='text-align: left; font-size:12px;'>Students registered</th><td>$numberOfStudents</td></tr><tr><th style='text-align: left; font-size:12px;'>NSIN Registration</th><td>$amountForNSIN</td></tr><tr><th style='text-align: left; font-size:12px;'>Logbook Registration</th><td>$amountForLogbook</td></tr><tr><th style='text-align: left; font-size:12px;'>Research Guideline Fee</th><td>$amountForResearch</td></tr><tr><th style='text-align: left; font-size:12px;'>Total Deduction</th><td>$totalDeductionFormatted</td></tr><tr><th style='text-align: left; font-size:12px;'>Remaining Balance</th><td>$remainingBalanceFormatted</td></tr></tbody></table>")->persistent(true)->toHtml();
            }

            DB::commit();

            // throw new Exception('Not Yet Implemented');
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();

            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', $th->getMessage())->persistent(true);
        }
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {

        $institutionId = $request->input('institution_id');
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty($name)) {
            $filterParams['filter[name]'] = $name;
        }

        if (!empty($gender)) {
            $filterParams['filter[gender]'] = $gender;
        }

        if (!empty($district)) {
            $filterParams['filter[district_id]'] = $district;
        }

        $url = route('platform.registration.nsin.applications.new', $filterParams);

        return redirect()->to($url);
    }

    // Function to generate a unique student code
    private function generateUniqueStudentCode($nsinPattern)
    {
        $lastStudent = NsinStudentRegistration::withoutGlobalScopes()
            ->where('nsin', 'LIKE', $nsinPattern . '%')
            ->orderBy('student_code', 'desc')
            ->first();

        if ($lastStudent) {
            $studentCode = str_pad((int) $lastStudent->student_code + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $studentCode = "001";
        }

        return $studentCode;
    }

}
