<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Transaction;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->title('Navigation')
                ->icon('bs.book')
                ->route(config('platform.index'))
                ->permission('platform.index'),

            Menu::make('Administration')
                ->icon('bs.archive')
                ->list([
                    Menu::make(__('Districts'))
                        ->icon('bs.house')
                        ->route('platform.systems.administration.districts'),

                    Menu::make(__('Institutions'))
                        ->icon('bs.house')
                        ->route('platform.systems.administration.institutions'),

                    Menu::make(__('Courses'))
                        ->icon('bs.house')
                        ->route('platform.systems.administration.courses'),

                    Menu::make(__('Papers'))
                        ->icon('bs.receipt')
                        ->route('platform.systems.administration.papers'),

                    Menu::make(__('Years'))
                        ->icon('bs.calendar')
                        ->route('platform.systems.administration.years'),
                ])
                ->permission('platform.systems.administration'),

            // Menu for Finance
            Menu::make(__('Finance'))
                ->icon('bs.book')
                ->list([

                    // Menu for Institution Accounts
                    Menu::make(__('Institution Accounts'))
                        ->route('platform.systems.finance.accounts')
                        ->permission('platform.systems.finance.accounts'),

                    // Completed Transactions
                    Menu::make('Institution Transactions')
                        ->route('platform.systems.finance.complete')
                        ->permission('platform.systems.finance.complete'),

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
                        ->permission('platform.systems.finance.pending')
                ]),

            // Menu for Continuous Assessment
            Menu::make(__('Continuous Assessment'))
                ->icon('bs.book')
                ->list([
                    Menu::make('Theory Assessment')
                        ->route('platform.systems.continuous-assessment.theory'),
                    Menu::make('Practical Assessment')
                        ->route('platform.systems.continuous-assessment.practical'),
                ])
                ->permission('platform.systems.continuous-assessment'),

            // Menu for Student Registration
            Menu::make('Student Registration')
                ->title('Registration')
                ->icon('bs.people')
                ->list([
                    Menu::make(__('Student Registrations'))
                        ->route('platform.systems.registration.students')
                        ->active(null),

                    Menu::make('Incomplete Registration')
                        ->route('platform.systems.registration.students', [
                            'status' => 'incomplete'
                        ])->active(null),


                    Menu::make('Accepted Registration')
                        ->route('platform.systems.registration.students', [
                            'status' => 'accepted'
                        ]),

                    Menu::make('Rejected Registration')
                        ->route('platform.systems.registration.students', [
                            'status' => 'rejected'
                        ]),

                    Menu::make('Approve/Decline Registration')
                        ->route('platform.systems.registration.students.approve')
                ])->permission('platform.systems.registration.students'),


            // Menu for Exam Registration
            Menu::make('Exam Registration')
                ->icon('bs.people')
                ->list([

                    Menu::make('Exam Registrations')
                        ->route('platform.systems.registration.exams'),

                    Menu::make('Incomplete Registration')
                        ->route('platform.systems.registration.exams', [
                            'status' => 'incomplete'
                        ]),

                    Menu::make('Accepted Registration')
                        ->route('platform.systems.registration.exams', [
                            'status' => 'accepted'
                        ]),

                    Menu::make('Rejected Registration')
                        ->route('platform.systems.registration.exams', [
                            'status' => 'rejected'
                        ]),

                    Menu::make('Approve/Decline Registration')
                        ->route('platform.systems.registration.exams.approve')
                ])->permission('platform.systems.registration.exams'),

            Menu::make('Biometrics')
                ->title('Biometrics')
                ->icon('bs.fingerprint')
                ->list([
                    Menu::make('Enrollment Log')
                        ->route('platform.system.biometrics.enrollment'),
                    Menu::make('Attendance Log')
                        ->route('platform.system.biometrics.access'),
                    Menu::make('Attendance Report')
                        ->route('platform.system.biometrics.report'),
                ]),

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

            Menu::make('User Guide')
                ->title('Documentation')
                ->icon('bs.box-arrow-up-right')
                ->url('#')
                ->target('_blank'),
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
            ItemPermission::group(__('System'))
                // Dashboard Permissions
                ->addPermission('platform.index', __('Dashboard'))

                // Continuous Assessment Permissions
                ->addPermission('platform.systems.continuous-assessment', __('Continuous Assessment'))

                // Administration Permissions
                ->addPermission('platform.systems.administration', __('Administration'))

                // Institution Accounts
                ->addPermission('platform.systems.finance.accounts', _('Institution Accounts'))

                // Finance Permissions
                ->addPermission('platform.systems.finance.pending', __('Pending Transactions'))

                // Finance Permissions
                ->addPermission('platform.systems.finance.complete', __('Completed Transactions'))

                // Student Registration Permissions
                ->addPermission('platform.systems.registration.students', __('Student Registration'))

                // Exam Registration Permissions
                ->addPermission('platform.systems.registration.exams', __('Exam Registration'))

                ->addPermission('platform.systems.institution.account_balance', __('Account Balance'))

                // System Users
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

        ];
    }
}
