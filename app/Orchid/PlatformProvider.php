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
            ]),

            Menu::make('Continuous Assessment')
            ->route('platform.assessment.continuous'),

            Menu::make('Surcharges & Fees')
            ->icon('bs.archive')
            ->list([
                Menu::make('Surcharges')->route('platform.administration.surcharge.list'),
                Menu::make('Surcharge Fees')->route('platform.administration.surcharge.fees')
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
            ->title('Registration'),

            Menu::make('Exam Registration')
            ->icon('bs.ticket')
            ->list([
                Menu::make('Exam Payments')
                ->route('platform.registration.period.nsin'),
            ]),

            Menu::make('Registration Periods')
            ->icon('bs.clock')
            ->list([
                Menu::make('NSIN Registration Period')
                ->route('platform.registration.period.nsin'),
                Menu::make('Exam Registration Period')
                ->route('platform.registration.period.exam'),
            ])->divider(),

            Menu::make('Manage Staff')
            ->icon('bs.people')
                ->route('platform.administration.staff')
                ->title('User Management'),

            Menu::make('Manage Students')
            ->icon('bs.people')
                ->route('platform.administration.student'),

            Menu::make(__('System Users'))
            ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users'),

            Menu::make(__('Roles & Permissions'))
            ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            // Menu::make('Documentation')
            // ->title('Docs')
            //     ->icon('bs.box-arrow-up-right')
            //     ->url('https://orchid.software/en/docs')
            //     ->target('_blank'),

            // Menu::make('Changelog')
            //     ->icon('bs.box-arrow-up-right')
            //     ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
            //     ->target('_blank')
            //     ->badge(fn () => Dashboard::version(), Color::DARK),

            Menu::make('Get Started')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index')),

            Menu::make('Sample Screen')
                ->icon('bs.collection')
                ->route('platform.example')
                ->badge(fn () => 6),

            Menu::make('Form Elements')
                ->icon('bs.card-list')
                ->route('platform.example.fields')
                ->active('*/examples/form/*'),

            Menu::make('Overview Layouts')
                ->icon('bs.window-sidebar')
                ->route('platform.example.layouts'),

            Menu::make('Grid System')
                ->icon('bs.columns-gap')
                ->route('platform.example.grid'),

            Menu::make('Charts')
                ->icon('bs.bar-chart')
                ->route('platform.example.charts'),

            Menu::make('Cards')
                ->icon('bs.card-text')
                ->route('platform.example.cards')
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
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
