<?php

namespace App\Orchid\Screens\Reports;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRegistrationReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::filters()
            ->select(
                'rp.id AS rp_id',
                'r.id AS r_id',
                'i.institution_name',
                'r.year_of_study',
                'r.completed',
                'r.verify',
                'r.approved',
                'sr.trial',
                'rp.reg_start_date',
                'rp.reg_end_date',
                'c.course_name',
            )
            ->from('registrations as r')
            ->join('institutions as i', 'i.id', '=', 'r.institution_id')
            ->join('student_registrations as sr', 'sr.registration_id', '=', 'r.id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->join('courses as c', 'c.id', '=', 'r.course_id')
            ->groupBy('r.id', 'i.institution_name', 'c.course_name', 'rp.id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved', 'trial');
            
            $query->orderBy('rp.id', 'desc');
            $query->orderBy('i.institution_name', 'asc');

        return [
            'report' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registration Report';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
                        ->fromModel(Institution::class, 'institution_name')
                        ->canSee(!auth()->user()->inRole('institution')),

                    Relation::make('course_id')
                        ->title('Filter By Program')
                        ->fromModel(Course::class, 'course_name'),

                    // Select Year of Study
                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
                            'Year 3 Semester 2' => 'Year 3 Semester 2',
                        ]),
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

            Layout::table('report', [
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('year_of_study', 'Year Of Study'),
                TD::make('reg_start_date', 'Registration Start Date'),
                TD::make('reg_end_date', 'Registration Start Date'),
                TD::make('pending', 'Pending')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->r_id,
                        'sr_flag' => 0
                    ])->count('id');
                }),
                TD::make('approved', 'Approved')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->r_id,
                        'sr_flag' => 1
                    ])->count('id');
                }),
                TD::make('rejected', 'Rejected')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->r_id,
                        'sr_flag' => 2
                    ])->count('id');
                }),
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
        $yearOfStudy = $request->input('year_of_study');

        $filters = [];

        if (!empty ($institutionId)) {
            $filters['filter[institution_id]'] = $institutionId;
        }

        if (!empty ($courseId)) {
            $filters['filter[course_id]'] = $courseId;
        }



        $url = route('platform.reports.exam_registration', $filters);

        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.reports.exam_registration');
    }
}
