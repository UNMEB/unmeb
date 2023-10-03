<?php

namespace App\Orchid\Screens\Biometric;

use App\Models\BiometricAccessLog;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AttendanceLogScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $attendanceLog = BiometricAccessLog::with('institution', 'course', 'paper');
        return [
            'attendance_log' => $attendanceLog->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Biometric Attendance Log';
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
            Layout::table('attendance_log', [
                TD::make('id', 'ID'),
                TD::make('institution_id', 'Institution')->render(fn (BiometricAccessLog $data) => $data->institution),
                TD::make('course_id', 'Course')->render(fn (BiometricAccessLog $data) => $data->course),
                TD::make('paper_id', 'Paper')->render(fn (BiometricAccessLog $data) => $data->paper),
                TD::make('verification_date', 'Verification Date')
                ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),
            ]),
        ];
    }

}
