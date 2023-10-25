<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class IncompleteNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Nsinregistration::filters()
            ->select(['nsin_registrations.id',
            'nsin_registrations.month',
            'courses.course_name',
            'institutions.institution_name',
            'institutions.id as institution_id',
            'courses.id as course_id',
            'years.year',
            DB::raw("(FLOOR(nsin_registrations.amount / 20000)) as students_to_register"),
            DB::raw("(SELECT COUNT(*) FROM nsin_student_registrations WHERE nsin_registration_id = nsin_registrations.id) as registered_students")
            ])
            ->from('nsin_registrations')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->join('years', 'nsin_registrations.year_id', '=', 'years.id')
            ->where('nsin_registrations.completed', 0)
            ->where('nsin_registrations.old', 0);

        return [
            'registrations' => $query
                ->orderBy('nsin_registrations.created_at', 'desc')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete NSIN Registration';
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
            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Filter By Institution')
                        ->fromModel(Institution::class, 'institution_name'),

                    Relation::make('course_id')
                        ->title('Filter By Program')
                        ->fromModel(Course::class, 'course_name'),

                    Select::make('month')
                        ->title('Filter By Month')
                        ->options([
                            'January' => 'January',
                            'February' => 'February',
                            'March' => 'March',
                            'April' => 'April',
                            'May' => 'May',
                            'June' => 'June',
                            'July' => 'July',
                            'August' => 'August',
                            'September' => 'September',
                            'October' => 'October',
                            'November' => 'November',
                            'December' => 'December',
                        ])
                        ->empty('None Selected'),

                    Select::make('year_id')
                        ->fromModel(Year::class, 'year')
                        ->title('Filter By Year')
                        ->empty('None Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),
            Layout::table('registrations', [
                TD::make('id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('month', 'Month'),
                TD::make('year', 'Year'),
                TD::make('students_to_register', 'Students to Register'),
                TD::make('registered_students', 'Registered Students'),
                TD::make('actions', 'Actions')->render(fn (NsinRegistration $data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
                ->route('platform.registration.nsin.incomplete.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'nsin_registration_id' => $data->id
                ]))
            ])
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $month = $request->input('month');
        $yearId = $request->input('year_id');

        $filters = [];

        if (!empty($institutionId)) {
            $filters['filter[institution_id]'] = $institutionId;
        }

        if (!empty($courseId)) {
            $filters['filter[course_id]'] = $courseId;
        }

        if (!empty($month)) {
            $filters['filter[month]'] = $month;
        }

        if (!empty($yearId)) {
            $filters['filter[year_id]'] = $yearId;
        }

        $url = route('platform.registration.nsin.incomplete', $filters);

        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.nsin.incomplete');
    }
}
