<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RemoveMisplacedRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-misplaced-registrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove misplaced registrations and corresponding transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all reversed transactions
        $reversedTransactions = Transaction::where('comment', 'LIKE', 'Reversal of Exam Registration Fee for Student ID:%')->get();

        $this->info('Found ' . $reversedTransactions->count() . ' transactions ready to be reversed');


    }
}
