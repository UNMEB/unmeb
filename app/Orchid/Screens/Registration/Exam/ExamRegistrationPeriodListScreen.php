<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\RegistrationPeriod;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRegistrationPeriodListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = RegistrationPeriod::query()
            ->orderBy('flag', 'desc');;
        return [
            'periods' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registration Periods';
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
                TD::make('reg_start_date', 'Start Date'),
                TD::make('reg_end_date', 'End Date'),
                TD::make('academic_year', 'Academic Year'),
                TD::make('flag', 'Is Active')->render(fn ($data) => $data->flag == 1 ? 'Active' : 'Inactive')
            ])
        ];
    }
}
