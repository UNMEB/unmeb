<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistrationPeriod;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NsinRegistrationPeriodListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistrationPeriod::with('year')->orderBy('flag', 'desc');
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
        return 'NSIN Registration Periods';
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
                TD::make('month', 'Month'),
                TD::make('year.year', 'Year'),
                TD::make('flag', 'Is Active'),
                TD::make('flag', 'Is Active')->render(fn ($data) => $data->flag == 1 ? 'Active' : 'Inactive')
            ])
        ];
    }
}
