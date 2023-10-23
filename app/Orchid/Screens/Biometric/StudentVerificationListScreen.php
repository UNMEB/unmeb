<?php

namespace App\Orchid\Screens\Biometric;

use App\Models\BiometricAccessLog;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentVerificationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = BiometricAccessLog::query();
        return [
            'access_log' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Biometric Access Log';
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
            Layout::table('access_log', [
                TD::make('id', 'ID'),
                TD::make('institution.institution_name', 'Institution'),
                TD::make('course.course_name', 'Program'),
                TD::make('paper.paper_name', 'Paper'),
                TD::make('verification_date', 'Verification Date'),
            ])
        ];
    }
}
