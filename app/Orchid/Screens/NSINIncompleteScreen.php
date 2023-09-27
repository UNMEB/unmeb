<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use App\Models\StudentRegistration;
use App\Models\StudentRegistrationNsin;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINIncompleteScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = $results = Nsinregistration::query()
            ->select('r.*')
            ->from('nsin_registrations AS r')
            ->join('institutions AS i', 'r.institution_id', '=', 'i.id')
            ->join('courses AS c', 'r.course_id', '=', 'c.id')
            ->join('years AS y', 'r.year_id', '=', 'y.id')
            ->where('r.completed', 0)
        ->where('r.old', 0)
        ->paginate();

        $query->getCollection()->transform(function ($row) {
            $nsinregistration_id = $row->id;

            $row->registered_students = StudentRegistrationNsin::query()
                ->where('nsin_registration_id', $nsinregistration_id)
                ->count();

            // dd($row->toJson());

            $fee = 20000;
            $row->total_students = floor($row->amount / $fee);

            return $row;
        });

        // dd($query->toJson());


        return [
            'registrations' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete NSIN Registrations';
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
            Layout::table('registrations', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(fn ($data) => optional($data->institution)->name),
                TD::make('course', 'Course')->render(fn ($data) => $data->course->name),
                TD::make('month', 'Month'),
                TD::make('year', 'Year')->render(fn (NsinRegistration $data) => $data->year->name),
                TD::make('registered_students', 'Number Registered'),
                TD::make('total_students', 'Number to Register '),
                TD::make('actions', 'Actions')->render(fn ($data) => Link::make(__('Edit'))
                ->route('platform.registration.nsin.incomplete.details', $data->id)
                    ->icon('bs.pencil'))

            ])
        ];
    }
}
