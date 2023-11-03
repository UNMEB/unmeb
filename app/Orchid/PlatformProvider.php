<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Transaction;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Dashboard'))
                ->route('platform.main')
                ->icon('fa.gauge')
                ->permission('platform.index'),

            // Continuous Assessment
            Menu::make(__('Continuous Assessment'))
                ->icon('fa.file-signature')
            ->list([Menu::make(_('Theory Assessment'))
                        ->route('platform.assessment.theory.list'),

                Menu::make(_('Practical Assessment'))
                        ->route('platform.assessment.practical.list'),


                Menu::make(_('Assessment Marks'))
                        ->route('platform.assessment.results'),
                ])
                ->permission('platform.assessment.list'),

            // Manage Students
            Menu::make(__('Manage Students'))
                ->route('platform.administration.students')
            ->icon('fa.user-graduate')
                ->permission('platform.administration.students.list'),

            // Manage Staff
            Menu::make(__('Manage Staff'))
            ->route('platform.administration.staff')
            ->icon('fa.user-graduate')
            ->permission('platform.administration.staff.list'),

            // Manage Student NSIN Registration
            Menu::make(__('Student NSIN Registration'))
                ->title('Registration')
                ->icon('fa.screen-users')
                ->list([
                    Menu::make(__('Incomplete NSIN Registrations'))
                        ->route('platform.registration.nsin.incomplete'),

                    Menu::make(__('Accepted NSIN Registrations'))
                        ->route('platform.registration.nsin.accepted'),

                    Menu::make(__('Rejected NSIN Registrations'))
                        ->route('platform.registration.nsin.rejected'),

                    Menu::make(__('Approve NSIN Registrations'))
                        ->route('platform.registration.nsin.approve'),
            ])
                ->permission('platform.registration.nsin.list'),

            // Manage Student Exam Registration
            Menu::make(__('Student Exam Registration'))
                ->icon('fa.cubes')
                ->list([
                    Menu::make(__('Incomplete Exam Registrations'))
                        ->route('platform.registration.exam.incomplete'),

                    Menu::make(__('Accepted Exam Registrations'))
                        ->route('platform.registration.exam.accepted'),

                    Menu::make(__('Rejected Exam Registrations'))
                        ->route('platform.registration.exam.rejected'),

                    Menu::make(__('Approve Exam Registrations'))
                        ->route('platform.registration.exam.approve'),
                ])
                ->permission('platform.registration.exam.list'),


            // Manage NSIN Registration Periods
            Menu::make(__('NSIN Registration Periods'))
                ->route('platform.registration.periods.nsin')
                ->title('Registration Periods')

            ->icon('fa.calendar-clock')
            ->permission('platform.registration.periods.nsin.list'),

            // Manage Exam Registration Periods
            Menu::make(__('Exam Registration Periods'))
                ->route('platform.registration.periods.exam')
                ->icon('fa.calendar-pen')
            ->permission('platform.registration.periods.exam.list')
                ->divider(),

            // Manage Finance
            Menu::make(__('Finance'))
                ->icon('bs.book')
                ->badge(function () {
                    $pendingTransactionCount = Transaction::where('is_approved', false)->count();
                    if ($pendingTransactionCount > 0) {
                        return $pendingTransactionCount;
                    }

                    return null;
                }, Color::DANGER)
                ->list([

                    // Menu for Institution Accounts
                    Menu::make(__('Institution Accounts'))
                        ->route('platform.systems.finance.accounts'),

                    // Completed Transactions
                    Menu::make('Institution Transactions')
                        ->route('platform.systems.finance.complete'),

                    // Pending Transactions
                    Menu::make('Pending Transactions')
                        ->route('platform.systems.finance.pending')
                        ->badge(function () {
                            $pendingTransactionCount = Transaction::where('is_approved', false)->count();
                            if ($pendingTransactionCount > 0) {
                                return $pendingTransactionCount;
                            }

                            return null;
                }, Color::DANGER)
                ])
                ->permission('platform.finance.list'),

            Menu::make('Reports')
                ->icon('bs.archive')
                ->title('Reports')
                ->list([
                    Menu::make('Packing List Report')
                        ->route('platform.reports.packing_list'),
                Menu::make('NSIN Registration Report')
                ->route('platform.reports.nsin_registration'),
                Menu::make('Exam Registration Report')
                ->route('platform.reports.exam_registration'),
                    Menu::make('Financial Report'),
                ])
                ->permission('platform.administration.reports.list')
                ->divider(),

            Menu::make('Surcharges & Fees')
                ->icon('bs.archive')
                ->list([
                    Menu::make('Surcharges')
                        ->route('platform.administration.surcharges'),
                    Menu::make('Surcharge Fees')
                        ->route('platform.administration.surcharge-fees'),
                ])
                ->permission('platform.administration.surcharges.list')
                ->divider(),

            Menu::make('Comments')
                ->icon('fa.comments')
            ->route('platform.comments.list')
            ->permission('platform.comments.list'),

            Menu::make(__('Users'))
                ->icon('bs.people')
            ->route('platform.systems.users')
            ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
            ->permission('platform.systems.roles')
                ->divider(),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            // Manage Continuous Assessment
            ItemPermission::group('Continuos Assessment')
            ->addPermission('platform.assessment.list', 'Access continuous Assessment'),

            // Manage Students
            ItemPermission::group('Manage Students')
                ->addPermission('platform.administration.students.list', 'View students')
                ->addPermission('platform.administration.students.create', 'Create students')
                ->addPermission('platform.administration.students.update', 'Update students')
                ->addPermission('platform.administration.students.delete', 'Delete students')
                ->addPermission('platform.administration.students.import', 'Import students')
                ->addPermission('platform.administration.students.export', 'Export students'),

            // Manage Staff
            ItemPermission::group('Manage Staff')
                ->addPermission('platform.administration.staff.list', 'View staff')
                ->addPermission('platform.administration.staff.create', 'Create staff')
                ->addPermission('platform.administration.staff.update', 'Update staff')
                ->addPermission('platform.administration.staff.delete', 'Delete staff')
                ->addPermission('platform.administration.staff.import', 'Import staff')
                ->addPermission('platform.administration.staff.export', 'Export staff'),

            // Manage NSIN Student Registration
            ItemPermission::group('Manage NSIN Registrations')
            ->addPermission('platform.registration.nsin.list', 'View NSIN Registrations')
            ->addPermission('platform.registration.nsin.create', 'Create NSIN Registrations')
            ->addPermission('platform.registration.nsin.update', 'Update NSIN Registrations')
            ->addPermission('platform.registration.nsin.delete', 'Delete NSIN Registrations')
            ->addPermission('platform.registration.nsin.import', 'Import NSIN Registrations')
            ->addPermission('platform.registration.nsin.export', 'Export NSIN Registrations'),

            // Manage Exam Studet Registration
            ItemPermission::group('Manage Exam Registrations')
            ->addPermission('platform.registration.exam.list', 'View Exam Registrations')
            ->addPermission('platform.registration.exam.create', 'Create Exam Registrations')
            ->addPermission('platform.registration.exam.update', 'Update Exam Registrations')
            ->addPermission('platform.registration.exam.delete', 'Delete Exam Registrations')
            ->addPermission('platform.registration.exam.import', 'Import Exam Registrations')
            ->addPermission('platform.registration.exam.export', 'Export Exam Registrations'),

            // Manage NSIN Registration Periods
            ItemPermission::group('NSIN Registration Periods')
            ->addPermission('platform.registration.periods.nsin.list', 'View NSIN Registration Periods')
            ->addPermission('platform.registration.periods.nsin.create', 'Create NSIN Registration Periods')
            ->addPermission('platform.registration.periods.nsin.update', 'Update NSIN Registration Periods')
            ->addPermission('platform.registration.periods.nsin.delete', 'Delete NSIN Registration Periods'),

            // Manage Exam Registration Periods
            ItemPermission::group('Exam Registration Periods')
            ->addPermission('platform.registration.periods.exam.list', 'View Exam Registration Periods')
            ->addPermission('platform.registration.periods.exam.create', 'Create Exam Registration Periods')
            ->addPermission('platform.registration.periods.exam.update', 'Update Exam Registration Periods')
            ->addPermission('platform.registration.periods.exam.delete', 'Delete Exam Registration Periods'),

            // Manage Institution Finance
            ItemPermission::group('Manage Finance')
                ->addPermission(
                    'platform.finance.list',
                    'View Institution Accounts'
                )
                ->addPermission('platform.finance.transactions.pending', 'View Pending Transactions')
                ->addPermission('platform.finance.transactions.complete', 'View Complete Transaction')
                ->addPermission('platform.finance.deposit', 'Deposit Funds')
                ->addPermission('platform.finance.approve', 'Approve Deposits')
                ->addPermission('platform.finance.transactions.reverse', 'Reverse Transactions')
                ->addPermission('platform.finance.transactions.flag', 'Flag Transaction'),

            // Manage Report
            ItemPermission::group('Manage Reports')
                ->addPermission('platform.administration.reports.list', 'View Reports')
                ->addPermission('platform.administration.reports.packing_list', 'View Packing List Report')
                ->addPermission('platform.administration.reports.nsin_registration', 'View NSIN Registration Report')
                ->addPermission('platform.administration.reports.exam_registration', 'View Exam Registration Report'),

            // Manage Surcharges  Surcharge Fees
            ItemPermission::group('Manage Surcharges & Fees')
            ->addPermission('platform.administration.surcharges.list', 'Manage Surcharges & Fees')
            ->addPermission('platform.administration.surcharges.types.list', 'View Surcharges')
            ->addPermission('platform.administration.surcharges.types.create', 'Create Surcharges')
            ->addPermission('platform.administration.surcharges.types.update', 'Update Surcharges')
            ->addPermission('platform.administration.surcharges.types.delete', 'Delete Surcharges')
            ->addPermission('platform.administration.surcharges.fees.list', 'View Surcharge Fees')
            ->addPermission('platform.administration.surcharges.fees.create', 'Create Surcharge Fee')
            ->addPermission('platform.administration.surcharges.fees.update', 'Update Surcharge Fee')
            ->addPermission('platform.administration.surcharges.fees.delete', 'Delete Surcharge Fee')
            ->addPermission('platform.administration.surcharges.fees.export', 'Export Surcharge Fees'),



            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group(__('Internals'))
            ->addPermission('platform.internals.all_institutions', 'View all institutions')
        ];
    }
}
