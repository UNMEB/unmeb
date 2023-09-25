<?php

declare(strict_types=1);

namespace App\Orchid;

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


            Menu::make('Administration')
                ->icon('bs.briefcase')
                ->title('Navigation')
                ->list([

                    Menu::make('Districts')
                        ->route('platform.administration.districts'),

                    Menu::make('Institutions')
                        ->route('platform.administration.institutions'),

                    Menu::make('Programs')
                        ->route('platform.administration.courses'),

                    Menu::make('Papers')
                        ->route('platform.administration.papers'),

                    Menu::make('Years')
                        ->route('platform.administration.years'),
                ])->divider(),

            Menu::make('NSIN Registration')
                ->icon('bs.wallet')
                ->list([
                    Menu::make('NSIN Payments')
                        ->route('platform.registration.nsin.payments'),

                    Menu::make('Incomplete Registration')
                        ->route('platform.registration.nsin.incomplete'),

                    Menu::make('Verify Registration')
                        ->route('platform.registration.nsin.verify'),

                    Menu::make('Accepted Registration')
                        ->route('platform.registration.nsin.accepted'),

                    Menu::make('Rejected Registration')
                        ->route('platform.registration.nsin.rejected'),

                    Menu::make('NSIN Rejection Reasons')
                        ->route('platform.registration.nsin.reasons'),

                    Menu::make('Verify Book Payments')
                        ->route('platform.registration.nsin.verify_books'),
                ])
                ->title('NSIN Registration')
                ->divider(),

            Menu::make('Exam Registration')
                ->icon('bs.wallet')
                ->list([
                    Menu::make('Exam Payments')
                        ->route('platform.registration.exam.payments'),

                    Menu::make('Incomplete Registration')
                        ->route('platform.registration.exam.incomplete'),

                    Menu::make('Verify Registration')
                        ->route('platform.registration.exam.verify'),

                    Menu::make('Accepted Registration')
                        ->route('platform.registration.exam.accepted'),

                    Menu::make('Rejected Registration')
                        ->route('platform.registration.exam.rejected'),

                    Menu::make('Exam Rejection Reasons')
                        ->route('platform.registration.exam.reasons'),
                ])
                ->title('NSIN Registration')
                ->divider(),

            Menu::make('Registration Periods')
            ->icon('bs.clock')
            ->list([
                Menu::make('NSIN Registration Period')
                ->route('platform.registration.period.nsin'),
                Menu::make('Exam Registration Period')
                ->route('platform.registration.period.exam'),
            ])->divider(),


            Menu::make('Continuous Assessment')
                ->icon('bs.archive')
                ->route('platform.assessment.continuous'),

            Menu::make('Surcharges & Fees')
                ->icon('bs.archive')
                ->list([
                    Menu::make('Surcharges')->route('platform.administration.surcharge.list'),
                    Menu::make('Surcharge Fees')->route('platform.administration.surcharge.fees')
                ])->divider(),


            Menu::make('Reports')
                ->title('Reports')
                ->icon('archive')
                ->list([
                // Year 1
                Menu::make('Packing List Year 1 Semester 1')
                    ->title('Year 1 Reports')
                    ->route('platform.reports.packing.year1.semester1'),
                Menu::make('Second Attempt Year 1 Semester 2')
                    ->route('platform.reports.attempt.year1.semester2'),
                Menu::make('Third Attempt Year 1 Semester 3')
                    ->route('platform.reports.attempt.year1.semester3'),

                // Year 2
                Menu::make('Packing List Year 2 Semester 1')
                ->title('Year 2 Reports')
                    ->route('platform.reports.packing.year2.semester1'),
                Menu::make('Second Attempt Year 2 Semester 2')
                ->route('platform.reports.attempt.year2.semester2'),
                Menu::make('Third Attempt Year 2 Semester 3')
                ->route('platform.reports.attempt.year2.semester3'),

                // Year 3
                Menu::make('Packing List Year 3 Semester 1')
                ->title('Year 3 Reports')
                    ->route('platform.reports.packing.year3.semester1'),
                Menu::make('Second Attempt Year 3 Semester 2')
                ->route('platform.reports.attempt.year3.semester2'),
                Menu::make('Third Attempt Year 3 Semester 3')
                ->route('platform.reports.attempt.year3.semester3'),
            ])
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
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
