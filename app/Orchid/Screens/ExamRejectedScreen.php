<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;

class ExamRejectedScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $registrations = Registration::with(['institution', 'course', 'registrationPeriod'])
        ->where('completed', 1)
        ->where('approved', 1)
        ->whereHas('registrationPeriod', function ($query) {
            $query->where('flag', 1);
        })
            ->get()->unique('registration_id');

        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Rejected Student Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
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
        return [];
    }
}
