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

            Menu::make(__('Continuous Assessment'))
                ->icon('fa.file-signature')
                ->list([
                    Menu::make(__('Theory Assessment'))
                        ->route('platform.assessment.theory.list'),

                    Menu::make(__('Practical Assessment'))
                        ->route('platform.assessment.practical.list'),

                    Menu::make(__('Assessment Marks'))
                        ->route('platform.assessment.results'),
                ])
                ->permission('platform.assessment'),




            Menu::make(__('Manage Students'))
                ->route('platform.administration.students')
                ->icon('fa.user')
                ->permission('platform.administration.students.list'),

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
                ]),

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
                ]),


            // Manage NSIN Registration Periods
            Menu::make(__('NSIN Registration Periods'))
                ->route('platform.registration.periods.nsin')
                ->title('Registration Periods')

                ->icon('fa.calendar-clock'),

            // Manage Exam Registration Periods
            Menu::make(__('Exam Registration Periods'))
                ->route('platform.registration.periods.exam')
                ->icon('fa.calendar-pen')
                ->divider(),

            // Manage Finance
            Menu::make(__('Finance'))
                ->icon('bs.book')
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

                ]),


            Menu::make('Reports')
                ->icon('bs.archive')
                ->title('Reports')
                ->list([
                    Menu::make('Packing List Report'),
                    Menu::make('NSIN Registration Report'),
                    Menu::make('Exam Registration Report'),
                    Menu::make('Financial Report'),
                ]),

            // Biometric Access
            Menu::make('Biometric Access')
                ->icon('fa.clock')
                ->list([
                    Menu::make('Verification Log')
                        ->route('platform.biometric.verification'),
                    Menu::make('Student Enrollment')
                        ->route('platform.biometric.enrollment'),
                ]),

            Menu::make('Surcharges & Fees')
                ->icon('bs.archive')
                ->list([
                    Menu::make('Surcharges')
                        ->route('platform.administration.surcharges'),
                    Menu::make('Surcharge Fees')
                        ->route('platform.administration.surcharge-fees'),
                ])->divider(),

            Menu::make('Comments')
                ->icon('fa.comments')
                ->route('platform.comments.list'),

            // Administration Menu
            Menu::make(__('Administration'))
                ->icon('fa.table-columns')
                ->title('Administration')
                ->list([
                    // Institutions
                    Menu::make(__('Institutions'))
                        ->route('platform.administration.institutions'),
                    // Programs
                    Menu::make(__('Programs'))->route('platform.administration.courses'),
                    // Papers
                    Menu::make(__('Papers'))->route('platform.administration.papers'),

                    // Years
                    Menu::make(__('Years'))->route('platform.administration.years'),
                    // Districts
                    Menu::make(__('Districts'))->route('platform.administration.districts'),
                ]),

            // Manage Staff
            Menu::make(__('Manage Staff'))
                ->route('platform.administration.staff')
                ->icon('fa.user-group'),



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
                ->addPermission('platform.assessment', 'Manage Continuous Assessment'),


            
        ];
    }
}
