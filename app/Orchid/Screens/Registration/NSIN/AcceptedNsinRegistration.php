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

class AcceptedNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::filters()->from('nsin_registrations AS nsin')
            ->join('institutions AS i', 'i.id', '=', 'nsin.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nsin.course_id')
            ->join('years AS y', 'y.id', '=', 'nsin.year_id')
            ->join('nsin_registration_periods AS rpn', 'nsin.year_id', '=', 'rpn.year_id')
            ->leftJoinSub(
                DB::table('nsin_registrations AS nsin')
                    ->join('nsin_student_registrations AS srn', 'nsin.id', '=', 'srn.nsin_registration_id')
                    ->join('students AS s', 'srn.student_id', '=', 's.id')
                    ->where('nsin.completed', 1)
                    ->groupBy('nsin.id')
                    ->select(
                        'nsin.id AS nsin_registration_id',
                        DB::raw('SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) AS registered_males'),
                        DB::raw('SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) AS registered_females'),
                        DB::raw('SUM(CASE WHEN srn.verify = 1 AND s.gender = "Male" THEN 1 ELSE 0 END) AS accepted_males'),
                        DB::raw('SUM(CASE WHEN srn.verify = 1 AND s.gender = "Female" THEN 1 ELSE 0 END) AS accepted_females')
                    ),
                'rc',
                'nsin.id',
                '=',
                'rc.nsin_registration_id'
            )
            ->select(
                'nsin.id AS nsin_registration_id',
                'i.id AS institution_id',
                'i.institution_name',
                'c.course_name',
                'c.id AS course_id',
                'nsin.MONTH',
                'nsin.year_id',
                'y.YEAR',
                'nsin.amount',
                'rc.registered_males',
                'rc.registered_females',
                'rc.accepted_males',
                'rc.accepted_females',
                DB::raw('(rc.registered_males + rc.registered_females) AS total_registered_students'),
                DB::raw('(rc.accepted_males + rc.accepted_females) AS total_accepted_students')
            )
            ->where('nsin.completed', 1)
            ->where('nsin.nsin_verify', 1)
            ->where('rpn.flag', 1);


        return [
            'registrations' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Accepted NSIN Registrations';
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
                TD::make('nsin_registration_id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('registered_males', 'Registered Males'),
                TD::make('registered_females', 'Registered Females'),
                TD::make('total_registered_students', 'Total Registered'),
                TD::make('accepted_males', 'Accepted Males'),
                TD::make('accepted_females', 'Accepted Females'),
                TD::make('total_accepted_students', 'Total Accepted'),
                TD::make('actions', 'Actions')->render(fn (NsinRegistration $data) => Link::make('Details')
                    ->class('btn btn-primary btn-sm link-primary')->route('platform.registration.nsin.accepted.details', [
                        'institution_id' => $data->institution_id,
                        'course_id' => $data->course_id,
                        'nsin_registration_id' => $data->nsin_registration_id
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

        $url = route('platform.registration.nsin.accepted', $filters);

        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.nsin.accepted');
    }
}
