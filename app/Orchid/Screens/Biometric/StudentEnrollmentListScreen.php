<?php

namespace App\Orchid\Screens\Biometric;

use App\Models\BiometricEnrollment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentEnrollmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = BiometricEnrollment::query();
        return [
            'enrollments' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Enrollment';
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
            Layout::table('enrollments', [
                TD::make('id', 'ID'),
                TD::make('student.fullName', 'Student'),
                TD::make('fingerprint_data', 'Fingerprint Data')->render(fn ($data) => $data->fingerprint_data != null ? 'Fingerprint Enrolled' : 'Fingerprint Not Enrolled'),
                TD::make('face_data', 'Face Data')->render(fn ($data) => $data->fingerprint_data != null ? 'Face Enrolled' : 'Face Not Enrolled'),
            ])
        ];
    }
}
