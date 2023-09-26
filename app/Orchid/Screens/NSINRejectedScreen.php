<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use Orchid\Screen\Screen;

class NSINRejectedScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::with([
            'institution',
            'course',
            'year',
            'studentRegistrationNsin' => function ($query) {
                $query->selectRaw('nsinregistration_id,
            SUM(CASE WHEN s.gender = "Female" AND students_registration_nsin.verify = 0 THEN 1 ELSE 0 END) as rejected_females,
            SUM(CASE WHEN s.gender = "Male" AND students_registration_nsin.verify = 0 THEN 1 ELSE 0 END) as rejected_males,
            SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) as registered_females,
            SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) as registered_males')
                ->join('students AS s', 's.student_id', '=', 'students_registration_nsin.student_id')
                ->groupBy('nsinregistration_id');
            }
        ])
            ->where('completed', 1)
            ->where('nsin_verify', 0)
            ->whereHas('registrationPeriodnsin', function ($query) {
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
        return 'Rejected NSIN Student Registrations';
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
        return [];
    }
}
