<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
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
        $query = Registration::select('registrations.*')
        ->join('institutions as i', 'registrations.institution_id', '=', 'i.id')
        ->join('registration_periods as rp', 'registrations.registration_period_id', '=', 'rp.id')
        ->join('courses as c', 'registrations.course_id', '=', 'c.id')
        ->join('surcharges as s', 'registrations.surcharge_id', '=', 's.id')
        ->join('surcharge_fees as sf', 's.id', '=', 'sf.surcharge_id')
        ->where('registrations.completed', 0)
        ->where('rp.flag', 1);

        // dd($query->first()->toJson());

        return ['records' => $query->paginate()];
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
            Layout::table('records', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(fn (Registration $data) => $data->institution->name),
                TD::make('course', 'Course')->render(fn (Registration $data) => $data->course->name),
                TD::make('year_of_study', 'Year of Study'),
                TD::make('start_date', 'Registration Start Date')->render(fn (Registration $data) => $data->registrationPeriod->start_date),
                TD::make('start_date', 'Registration End Date')->render(fn (Registration $data) => $data->registrationPeriod->start_date),
            ])
        ];
    }
}
