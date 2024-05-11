<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Models\LogbookFee;
use App\Models\Student;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Models\Institution;
use Artisan;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RecalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:recalculate-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate account balances based on transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Put the application into maintenance mode
        Artisan::call('down');

        // DB::beginTransaction();

        try {

            $this->info('Recalculating institutions accounts balances for ' . Institution::withoutGlobalScopes()->count() . ' institutions');

            $institutions = Institution::withoutGlobalScopes()->get();

            foreach ($institutions as $institution) {
                $account = Account::withoutGlobalScopes()
                    ->where('institution_id', $institution->id)
                    ->first();

                if (!$account) {
                    // If account doesn't exist, create a new one with initial balance of zero
                    $account = new Account();
                    $account->institution_id = $institution->id;
                    $account->balance = 0;
                    $account->save();
                }

                // Get the current account balance
                $this->info('Current account balance for ' . $institution->institution_name . ' with ID ' . $institution->id . ' is UGX ' . number_format($account->balance));

                // Delete all transactions that are not approved by 273
                Transaction::withoutGlobalScopes()
                    ->whereNotIn('status', ['pending', 'declined'])
                    ->where('institution_id', $institution->id)
                    ->where(function ($query) {
                        $query->where('approved_by', '!=', 273)
                            ->orWhereNull('approved_by');
                    })
                    ->delete();


                // Recalculate amount spent on NSIN registrations
                $nsins = Student::withoutGlobalScopes()
                    ->select(
                        'nr.id as nr_id',
                        'nr.course_id',
                        'nsr.created_at',
                        'nsr.student_id'
                    )
                    ->from('students AS s')
                    ->join('nsin_student_registrations as nsr', 'nsr.student_id', '=', 's.id')
                    ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
                    ->join('nsin_registration_periods as nrp', function ($join) {
                        $join->on('nr.year_id', '=', 'nrp.year_id')
                            ->on('nr.month', '=', 'nrp.month');
                    })
                    ->where('nrp.flag', 1)
                    ->where('nr.institution_id', $institution->id)
                    ->get();

                $this->info('Found ' . $nsins->count() . ' NSIN Registrations for ' . $institution->institution_name);

                // Get all the fees
                $settings = \Config::get('settings');
                $nsinRegistrationFee = $settings['fees.nsin_registration'];

                // Initialize total fees with NSIN registration fee and logbook fee
                $totalNsinFees = 0;
                $totalLogbookFees = 0;
                $totalResearchFees = 0;

                foreach ($nsins as $nsin) {
                    $courseId = $nsin->course_id;
                    $logbookFee = LogbookFee::firstWhere('course_id', $courseId);
                    $courseCode = Course::where('id', $courseId)->value('course_code');
                    $isDiplomaCourse = Str::startsWith($courseCode, ['A', 'D']);

                    $totalNsinFees += $nsinRegistrationFee;
                    $totalLogbookFees += $logbookFee->course_fee;
                    if ($isDiplomaCourse) {
                        $researchGuidelineFee = $settings['fees.research_fee'];
                        $totalResearchFees += $researchGuidelineFee;
                    }

                    // Create a new transaction for NSIN registration fee as a debit
                    $this->createTransaction($account, $institution, $nsinRegistrationFee, 'NSIN REGISTRATION FEE FOR STUDENT ID: ' . $nsin->student_id, $nsin->created_at);

                    // Create a new transaction for logbook fee as a debit
                    $this->createTransaction($account, $institution, $logbookFee->course_fee, 'LOGBOOK REGISTRATION FEE FOR STUDENT ID: ' . $nsin->student_id, $nsin->created_at);

                    if ($isDiplomaCourse) {
                        // Create a new transaction for research fee as a debit
                        $this->createTransaction($account, $institution, $settings['fees.research_fee'], 'RESEARCH REGISTRATION FEE FOR STUDENT ID: ' . $nsin->student_id, $nsin->created_at);
                    }
                }

                $totalNSINRegistrationFees = $totalNsinFees + $totalLogbookFees + $totalResearchFees;
                $this->info('NSIN fees are ' . number_format($totalNSINRegistrationFees));

                // Calculate cost per paper
                $costPerPaper = (float) $settings['fees.paper_registration'];

                // Recalculate amount spent on exam registrations
                $exams = Student::withoutGlobalScopes()
                    ->select(
                        'r.id as r_id',
                        'r.course_id',
                        'sr.trial',
                        'sr.no_of_papers',
                        's.id AS student_id',
                        'sr.created_at'
                    )
                    ->from('students AS s')
                    ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
                    ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
                    ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
                    ->where('rp.flag', 1)
                    ->where('r.institution_id', $institution->id)
                    ->get();

                $totalExamFees = 0;

                foreach ($exams as $exam) {
                    $courseId = $exam->course_id;

                    // Get surcharge for normal registration
                    $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
                        ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
                        ->where('surcharge_fees.course_id', $courseId)
                        ->where('surcharges.flag', 1)
                        ->first();

                    $trial = $exam->trial;
                    $numberOfPapers = $exam->no_of_papers;

                    if ($trial == 'First') {
                        $totalExamFees += $normalCharge->course_fee;
                    } else if ($trial == 'Second' || $trial == 'Third') {
                        $totalExamFees += $costPerPaper * $numberOfPapers;
                    }

                    // Create a new transaction for exam registration fee as a debit
                    $transactionAmount = $trial == 'First' ? $normalCharge->course_fee : $costPerPaper * $numberOfPapers;
                    $this->createTransaction($account, $institution, $transactionAmount, 'EXAM REGISTRATION FEE FOR STUDENT ID: ' . $exam->student_id, $exam->created_at);
                }

                $this->info('Found ' . $exams->count() . ' Exam Registrations for ' . $institution->institution_name);
                $this->info('Exam fees are ' . number_format($totalExamFees));

                // Top up account balance with funds approved by Semei
                $approvedFunds = Transaction::withoutGlobalScopes()
                    ->where('account_id', $account->id)
                    ->where('status', 'approved')
                    ->where('type', 'credit')
                    ->where('approved_by', 273)
                    ->sum('amount');

                // Update account balance to new balance
                $account->balance = $approvedFunds;

                // Deduct fees
                $totalDebits = $totalNSINRegistrationFees + $totalExamFees;
                $account->balance -= $totalDebits;

                $account->save();

                $this->info('Summary:');
                $this->info('--------------------------------------------');
                $this->info('Total Approved Funds: ' . number_format($approvedFunds));
                $this->info('Total Debits: ' . number_format($totalDebits));
                $this->info('Total Credits: ' . number_format($approvedFunds));
                $this->info('--------------------------------------------');
                $this->info('New Account Balance: ' . number_format($account->balance));
                $this->info('--------------------------------------------');
            }

            // DB::commit();
        } catch (\PDOException $e) {
            // DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());

            throw $e;
        }

        // Bring the application back up
        Artisan::call('up');
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
