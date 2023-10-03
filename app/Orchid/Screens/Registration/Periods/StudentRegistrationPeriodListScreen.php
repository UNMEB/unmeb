<?php

namespace App\Orchid\Screens\Registration\Periods;

use App\Models\StudentRegistrationPeriod;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentRegistrationPeriodListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'periods' => StudentRegistrationPeriod::latest()->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Registration Periods';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
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
            Layout::table('periods', [
                TD::make('id', 'ID'),
                TD::make('id', 'ID'),
                TD::make('id', 'ID'),
                TD::make('id', 'ID'),
            ])
        ];
    }
}
