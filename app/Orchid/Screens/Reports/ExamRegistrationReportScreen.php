<?php

namespace App\Orchid\Screens\Reports;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
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
                'registration_periods.id AS registration_period_id',
                'institutions.institution_name',
                'registrations.year_of_study',
                'registrations.completed',
                'registrations.verify',
                'registrations.approved',
                'student_registrations.trial',
                'registration_periods.reg_start_date',
                'registration_periods.reg_end_date',
                'courses.course_name',
            )
            ->selectRaw('COUNT(student_registrations.id) as registered_students')
            ->join('institutions', 'institutions.id', '=', 'registrations.institution_id')
            ->join('student_registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('registration_periods', 'registration_periods.id', '=', 'registrations.registration_period_id')
            ->join('courses', 'courses.id', '=', 'registrations.course_id')
            ->groupBy('registrations.id', 'institutions.institution_name', 'courses.course_name', 'registration_periods.id', 'registration_periods.reg_start_date', 'registration_periods.reg_end_date', 'registrations.completed', 'registrations.verify', 'registrations.approved', 'trial')
            ->paginate();

        return [
            'report' => $query
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
                TD::make('registered_students', 'Students Registered'),
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
