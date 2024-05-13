<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Models\LogbookFee;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\TransactionMeta;
use App\Orchid\Layouts\NSINRegistrationTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use RealRashid\SweetAlert\Facades\Alert;

class NSINRegistrationsDetailScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        // Check and handle null values for keys
        $registration_period_id = $request->get('registration_period_id');
        $registration_id = $request->get('registration_id');
        $institution_id = $request->get('institution_id');
        $course_id = $request->get('course_id');

        session()->put('registration_period_id', $registration_period_id);
        session()->put('registration_id', $registration_id);
        session()->put('institution_id', $institution_id);
        session()->put('course_id', $course_id);


        $query = NsinRegistrationPeriod::select(
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            's.district_id',
            's.country_id',
            's.location',
            's.passport_number',
            's.nin',
            's.telephone',
            's.refugee_number',
            's.lin',
            's.nsin as nsin',
            's.passport'
        )
            ->from('nsin_registration_periods as rp')
            ->join('nsin_registrations AS r', function ($join) {
                $join->on('rp.month', '=', 'r.month');
                $join->on('rp.year_id', '=', 'r.year_id');
            })
            ->join('nsin_student_registrations AS sr', 'r.id', '=', 'sr.nsin_registration_id')
            ->join('students as s', 'sr.student_id', '=', 's.id')
            ->where('rp.id', $registration_period_id)
            ->where('r.institution_id', $institution_id)
            ->where('r.course_id', $course_id)
            ->orderBy('sr.nsin', 'desc');

        return [
            'students' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            DropDown::make('Select Action')
                ->class('btn btn-primary btn-md')
                ->list([
                    Button::make('Rollback NSIN Registration')
                        ->icon('bs.receipt')
                        ->class('btn link-success')
                        ->method('rollback')
                        ->canSee(auth()->user()->inRole('administrator')),

                ])
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $table = (new NSINRegistrationTable);
        return [
            $table
        ];
    }

    public function rollback(Request $request)
    {
        DB::beginTransaction();

        try {

            $registration_id = session()->get('registration_id');

            // Get Student IDs from form
            $studentIds = collect($request->get('students'))->values();

            $nsinRegistration = NsinRegistration::findOrFail($registration_id);

            $institution = Institution::findOrFail($nsinRegistration->institution_id);

            $course = Course::findOrFail($nsinRegistration->course_id);

            $settings = \Config::get('settings');
            $nsinRegistrationFee = $settings['fees.nsin_registration'];
            $logbookFee = LogbookFee::firstWhere('course_id', $course->id);

            // Initialize total fees with NSIN registration fee and logbook fee
            $totalNsinFees = 0;
            $totalLogbookFees = 0;
            $totalResearchFees = 0;

            if ($nsinRegistrationFee == 0 || is_null($nsinRegistrationFee)) {
                throw new Exception('NSIN Student registration fee not yet set. Please contact support at UNMEB');
            }

            if (!$logbookFee || $logbookFee->course_fee == 0) {
                throw new Exception("Unable to register students at the moment. The logbook fees for this course are not yet set. Please contact UNMEB Support", 1);
            }

            $courseId = NsinRegistration::find($registration_id)->course_id;

            // Check if the course is a diploma course
            $courseCode = Course::where('id', $courseId)->value('course_code');
            $isDiplomaCourse = Str::startsWith($courseCode, ['A', 'D']);


            foreach ($studentIds as $studentId) {

                $registrationPeriod = RegistrationPeriod::whereFlag('1', true)->first();

                // Get the current regisration
                $currentRegistration = Registration::where('registration_period_id', $registrationPeriod->id)->first();

                // Check if student has a registration in current period
                $studentExamReg = StudentRegistration::where('student_id', $studentId)
                    ->where('registration_id', $currentRegistration->id)
                    ->first();

                if (!is_null($studentExamReg)) {
                    throw new Exception('Cant rollback student with an active Exam Student registration');
                }

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
            }

            // Get the overall total for all students
            $overallTotal = $totalNsinFees + $totalLogbookFees + $totalResearchFees;

            foreach ($studentIds as $studentId) {
                // Find the registration and delete it
                NsinStudentRegistration::where([
                    'student_id' => $studentId,
                    'nsin_registration_id' => $registration_id,
                ])->delete();

                $this->createTransaction(
                    $institution->account,
                    $institution,
                    $nsinRegistrationFee,
                    'REVERSED NSIN REGISTRATION FEE FOR STUDENT ID: ' . $studentId,
                    now()
                );

                $this->createTransaction(
                    $institution->account,
                    $institution,
                    $logbookFee->course_fee,
                    'REVERSED LOGBOOK REGISTRATION FEE FOR STUDENT ID: ' . $studentId,
                    now()
                );

                if ($isDiplomaCourse) {

                    $researchGuidelineFee = $settings['fees.research_fee'];

                    $this->createTransaction(
                        $institution->account,
                        $institution,
                        $researchGuidelineFee,
                        'REVERSED RESEARCH REGISTRATION FEE FOR STUDENT ID: ' . $studentId,
                        now()
                    );
                }
            }

            $remainingBalance = $institution->account->balance + $overallTotal;

            $institution->account->update([
                'balance' => $remainingBalance,
            ]);

            $amountForNSIN = 'Ush ' . number_format($totalNsinFees);
            $amountForLogbook = 'Ush ' . number_format($totalLogbookFees);
            $amountForResearch = 'Ush ' . number_format($totalResearchFees);
            $totalDeductionFormatted = 'Ush ' . number_format($overallTotal);
            $remainingBalanceFormatted = 'Ush ' . number_format($remainingBalance);
            $numberOfStudents = count($studentIds);

            DB::commit();

            Alert::success('Action Completed', "<table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'><tbody><tr><th style='text-align: left; font-size:12px;'>NSINs Reversed</th><td>$numberOfStudents</td></tr><tr><th style='text-align: left; font-size:12px;'>NSIN Reversal</th><td>$amountForNSIN</td></tr><tr><th style='text-align: left; font-size:12px;'>Logbook Reversal</th><td>$amountForLogbook</td></tr><tr><th style='text-align: left; font-size:12px;'>Research Guideline Reversal</th><td>$amountForResearch</td></tr><tr><th style='text-align: left; font-size:12px;'>Total Reversal</th><td>$totalDeductionFormatted</td></tr><tr><th style='text-align: left; font-size:12px;'>New Account Balance</th><td>$remainingBalanceFormatted</td></tr></tbody></table>")->persistent(true)->toHtml();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;

            Alert::error('Action Failed', 'Unable to complete NSIN reversal for selected students. Failed with error ' . $th->getMessage());
        }
    }

    public function delete(Request $request)
    {
    }

    private function parseUserAgent($userAgent)
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($userAgent);
        return $agent->browser() . ' on ' . $agent->platform();
    }

    private function getNetworkMeta($ip)
    {
        try {
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
        } catch (\Throwable $th) {
            return [];
        }
    }

    /**
     * Create a new transaction.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Institution  $institution
     * @param  float  $amount
     * @param  string  $comment
     * @param  string  $createdAt
     * @return void
     */
    protected function createTransaction($account, $institution, $amount, $comment, $createdAt)
    {
        // Create a new transaction
        $transaction = new Transaction();
        $transaction->amount = $amount;
        $transaction->type = 'debit';
        $transaction->status = 'approved';
        $transaction->account_id = $account->id;
        $transaction->institution_id = $institution->id;
        $transaction->comment = $comment;
        $transaction->setCreatedAt($createdAt);
        $transaction->saveQuietly();
    }
}
