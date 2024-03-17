<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Account;
use App\Models\BiometricEnrollment;
use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinStudentRegistration;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Models\User;
use App\View\Components\Chart;
use App\View\Components\GenderDistributionByCourseChart;
use App\View\Components\GenderDistributionChart;
use App\View\Components\InstitutionDistributionByCategoryChart;
use App\View\Components\InstitutionDistributionByTypeChart;
use App\View\Components\StudentRegistrationByCourseBarChart;
use App\View\Components\StudentRegistrationByInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $institutionId = $this->currentUser()->institution_id;
        // $account = Account::where('institution_id', $institutionId)->first();
        // // dd($account);

        return [
            'metrics' => [
                'account_balance' => number_format((float) Account::where('institution_id', $institutionId)->sum('balance'), 0),
                'account_expenditure' => number_format((float) Transaction::where('institution_id', $institutionId)
                    ->where('type', 'debit')
                    ->sum('amount'), 0),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        $institution = $this->currentUser()->institution;

        if ($institution) {
            return $institution->institution_name;
        }

        return 'Uganda Nurses And Midwives Examination Board';
    }

    /**
     * The description of the screen displayed in the header
     */
    public function description(): ?string
    {

        $institution = $this->currentUser()->institution;

        if ($institution) {
            return 'View your institution statistics, manage student and staff data, handle registration and transactions';
        }

        return 'View metrics, charts and various reports of Institutions, Programs, Papers, Staff, Students, and registration data.';
    }


    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar()
    {
        return [
            Button::make('Downalod User Manual')
                ->class('btn btn-success')
                ->rawClick(false),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $metrics = [];

        $graphs = [];

        if ($this->currentUser()->inRole('institution')) {
            $metrics = [
                'Account Balance (UGX)' => 'metrics.account_balance',
                'Total Expenditure' => 'metrics.account_expenditure',
            ];
        }

        return [
            Layout::metrics($metrics),
        ];
    }

    public function currentUser(): User
    {
        return auth()->user();
    }

}
