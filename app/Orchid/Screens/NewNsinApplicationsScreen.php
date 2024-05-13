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
use App\Models\TransactionLog;
use App\Models\TransactionMeta;
use App\Orchid\Layouts\RegisterStudentsForNSINForm;
use App\Orchid\Layouts\RegisterStudentsForNSINTable;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $registeredStudentIds = Student::withoutGlobalScopes()
            ->from('students as s')
            ->join('nsin_student_registrations as nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->join('institutions as i', 'i.id', '=', 'nr.institution_id')
            ->join('nsin_registration_periods as nrp', function ($join) {
                $join->on('nr.year_id', '=', 'nrp.year_id')
                    ->on('nr.month', '=', 'nrp.month');
            })
            ->whereColumn('nsr.student_id', 's.id')
            ->where('nr.institution_id', session('institution_id'))
            ->where('nrp.flag', 1)
            ->pluck('s.id')
            ->toArray();

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
            ->where('s.institution_id', session('institution_id'))
            ->whereNotIn('s.id', session('selected_student_ids', []))
            ->whereNotIn('s.id', $registeredStudentIds);

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
            $nrpID = session('nsin_registration_period_id');
            $institutionId = session('institution_id');
            $courseId = session('course_id');

            $settings = \Config::get('settings');
            $nsinRegistrationFee = $settings['fees.nsin_registration'];
            $logbookFee = LogbookFee::firstWhere('course_id', $courseId);

            if ($nsinRegistrationFee == 0 || is_null($nsinRegistrationFee)) {
                throw new Exception('NSIN Student registration fee not yet set. Please contact support at UNMEB');
            }

            if (!$logbookFee || $logbookFee->course_fee == 0) {
                throw new Exception("Unable to register students at the moment. The logbook fees for this course are not yet set. Please contact UNMEB Support", 1);
            }

            // Check if the course is a diploma course
            $courseCode = Course::where('id', $courseId)->value('course_code');
            $isDiplomaCourse = Str::startsWith($courseCode, ['A', 'D']);

            $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);
            $yearId = $nsinRegistrationPeriod->year_id;
            $month = $nsinRegistrationPeriod->month;

            $institution = Institution::findOrFail($institutionId);

            // Get the student keys from the request
            $students = $request->get('students');

            if (empty($students)) {
                throw new Exception('Unable to submit data. You have not selected any students to register');
            }


            $numberOfStudents = count($students);

            $totalNsinFees = 0;
            $totalLogbookFees = 0;
            $totalResearchFees = 0;

            foreach ($students as $studentId) {
                // Calculate NSIN registration fee for each student
                $totalNsinFees += $nsinRegistrationFee;

                // Calculate logbook fee for each student
                $totalLogbookFees += $logbookFee->course_fee;

                // If it's a diploma course, calculate research guideline fee for each student
                if ($isDiplomaCourse) {
                    $researchGuidelineFee = $settings['fees.research_fee'];

                    if ($researchGuidelineFee == 0 || is_null($researchGuidelineFee)) {
                        throw new Exception('Research Guideline fees not yet set. Please contact support at UNMEB');
                    }

                    $totalResearchFees += $researchGuidelineFee;
                }

                // Create NSIN Registration For this student
                $nsinRegistration = NsinRegistration::firstOrCreate([
                    'institution_id' => $institution->id,
                    'course_id' => $courseId,
                    'month' => $month,
                    'year_id' => $yearId,
                ]);

                // Find if this student code already exists
                $existingStudentRegistration = NsinStudentRegistration::withoutGlobalScopes()
                    ->where('student_id', $studentId)
                    ->where('nsin_registration_id', $nsinRegistration->id)
                    ->first();

                if (!$existingStudentRegistration) {
                    // Create new NSIN Student Registration
                    $nsinStudentRegistration = new NsinStudentRegistration();
                    $nsinStudentRegistration->nsin_registration_id = $nsinRegistration->id;
                    $nsinStudentRegistration->student_id = $studentId;
                    $nsinStudentRegistration->verify = 0;
                    $nsinStudentRegistration->save();
                }


                // Create NSIN transaction for this student
                $nsinTransaction = new Transaction([
                    'amount' => $nsinRegistrationFee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'NSIN REGISTRATION FOR 1 STUDENT'
                ]);
                $nsinTransaction->saveQuietly();

                // Create transaction log for NSIN registration
                $nsinTransactionLog = TransactionLog::create([
                    'transaction_id' => $nsinTransaction->id,
                    'user_id' => auth()->user()->id,
                    'action' => 'created',
                    'description' => 'NSIN REGISTRATION'
                ]);

                // Create logbook transaction for this student
                $logbookTransaction = new Transaction([
                    'amount' => $logbookFee->course_fee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'LOGBOOK REGISTRATION FOR 1 STUDENT'
                ]);
                $logbookTransaction->saveQuietly();

                // Create transaction log for logbook registration
                $logbookTransactionLog = TransactionLog::create([
                    'transaction_id' => $logbookTransaction->id,
                    'user_id' => auth()->user()->id,
                    'action' => 'created',
                    'description' => 'LOGBOOK REGISTRATION'
                ]);

                // Create transaction meta for logbook registration
                $logbookTransactionMeta = TransactionMeta::create([
                    'transaction_id' => $logbookTransaction->id,
                    'key' => 'logbook_registration_info',
                    'value' => [
                        'student' => $studentId,
                    ]
                ]);

                $researchTransaction = null;

                // Create research transaction for this student if applicable
                if ($isDiplomaCourse) {
                    $researchTransaction = new Transaction([
                        'amount' => $researchGuidelineFee,
                        'type' => 'debit',
                        'account_id' => $institution->account->id,
                        'institution_id' => $institution->id,
                        'initiated_by' => auth()->user()->id,
                        'status' => 'approved',
                        'comment' => 'RESEARCH GUIDELINES FOR 1 STUDENT'
                    ]);

                    $researchTransaction->saveQuietly();

                    // Create transaction log for research guidelines
                    $researchTransactionLog = TransactionLog::create([
                        'transaction_id' => $researchTransaction->id,
                        'user_id' => auth()->user()->id,
                        'action' => 'created',
                        'description' => 'RESEARCH GUIDELINES REGISTRATION'
                    ]);

                    // Create transaction meta for research guidelines
                    $researchTransactionMeta = TransactionMeta::create([
                        'transaction_id' => $researchTransaction->id,
                        'key' => 'research_guidelines_registration_info',
                        'value' => [
                            'student' => $studentId,
                        ]
                    ]);


                }

                // Create transaction meta for NSIN registration
                $nsinTransactionMeta = TransactionMeta::create([
                    'transaction_id' => $nsinTransaction->id,
                    'key' => 'nsin_registration_info',
                    'value' => [
                        'nsin_registration_id' => $nsinRegistration->id,
                        'student' => $studentId,
                        'logbook_transaction_id' => $logbookTransaction->id,
                        'research_transaction_id' => $researchTransaction ? $researchTransaction->id : null,
                    ]
                ]);
            }

            $totalRegistrationFee = $totalNsinFees + $totalLogbookFees + $totalResearchFees;

            if ($totalRegistrationFee > $institution->account->balance) {
                throw new Exception('Account balance too low to complete this transaction. Please top up to continue');
            }

            $institution->account->update([
                'balance' => $institution->account->balance - $totalRegistrationFee,
            ]);

            $amountForNSIN = 'Ush ' . number_format($totalNsinFees);
            $amountForLogbook = 'Ush ' . number_format($totalLogbookFees);
            $amountForResearch = 'Ush ' . number_format($totalResearchFees);
            $totalDeductionFormatted = 'Ush ' . number_format($totalRegistrationFee);
            $remainingBalance = $institution->account->balance;
            $remainingBalanceFormatted = 'Ush ' . number_format($remainingBalance);

            DB::commit();

            \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', "<table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'><tbody><tr><th style='text-align: left; font-size:12px;'>Students registered</th><td>$numberOfStudents</td></tr><tr><th style='text-align: left; font-size:12px;'>NSIN Registration</th><td>$amountForNSIN</td></tr><tr><th style='text-align: left; font-size:12px;'>Logbook Registration</th><td>$amountForLogbook</td></tr><tr><th style='text-align: left; font-size:12px;'>Research Guideline Fee</th><td>$amountForResearch</td></tr><tr><th style='text-align: left; font-size:12px;'>Total Deduction</th><td>$totalDeductionFormatted</td></tr><tr><th style='text-align: left; font-size:12px;'>Remaining Balance</th><td>$remainingBalanceFormatted</td></tr></tbody></table>")->persistent(true)->toHtml();

        } catch (\Throwable $th) {
            DB::rollBack();
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', 'Unable to complete NSIN registration for selected students. Failed with error ' . $th->getMessage());
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

    private function parseUserAgent($userAgent)
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($userAgent);
        return $agent->browser() . ' on ' . $agent->platform();
    }

    private function getNetworkMeta($ip)
    {
        // Check if testing offline
        if ($ip === '127.0.0.1') {
            return [];
        }

        $response = Http::get('http://ip-api.com/json/' . $ip);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'country' => $data['country'],
                'country_code' => $data['countryCode'],
                'region' => $data['regionName'],
                'city' => $data['city'],
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
                'timezone' => $data['timezone'],
                'isp' => $data['isp'],
                'organization' => $data['org'],
                'as' => $data['as']
            ];
        }

        return [];
    }

}
