<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ExamIncompleteScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::with(['institution', 'registrationPeriod', 'course', 'surcharge', 'surchargeFee'])
        ->where('completed', 0)
        ->whereHas('registrationPeriod', function ($query) {
            $query->where('flag', 1);
        });

        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Import Data'),
            Button::make('Export Data')
            ->icon('export')
            ->method('export')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('registrations', [])
        ];
    }
}
