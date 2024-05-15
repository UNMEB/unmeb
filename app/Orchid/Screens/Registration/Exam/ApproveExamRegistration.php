<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ApproveExamRegistration extends Screen
{
    public $period;
    public $activePeriod;
    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->filters = $request->get("filter");

        $queryPeriod = $request->query('period');

        if (is_null($queryPeriod)) {
            $this->activePeriod = RegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        if (!is_null($queryPeriod)) {
            $this->activePeriod = $queryPeriod;
        }

        $query = Student::withoutGlobalScopes()
            ->select(
                'r.id as registration_id',
                'i.id as institution_id',
                'i.institution_name',
                'c.id as course_id',
                'c.course_name',
                'year_of_study as semester',
                'reg_start_date as start_date',
                'reg_end_date as end_date',
                'sr.trial'
            )
            ->from('students AS s')
            ->join('student_registrations As sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->join('institutions AS i', 'i.id', '=', 'r.institution_id')
            ->join('courses AS c', 'c.id', '=', 'r.course_id')
            ->groupBy('i.institution_name', 'i.id', 'c.course_name', 'c.id', 'registration_id', 'sr.trial');

        $query->where('rp.id', $this->activePeriod);

        $query->orderBy('institution_name', 'asc');
        $query->orderBy('course_name', 'desc');
        $query->orderBy('semester', 'asc');

        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }


        return [
            'applications' => $query->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if (!is_null($this->activePeriod)) {
            $period = RegistrationPeriod::select('*')
                ->where('id', $this->activePeriod)
                ->where('flag', 1)
                ->first();

            return 'Exam Approval for ' . $period->reg_start_date->format('Y-m-d') . ' / ' . $period->reg_end_date->format('Y-m-d');
        }

        return 'Exam Approval';
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
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->chunk(20),

                    Relation::make('course_id')
                        ->title('Select Course')
                        ->fromModel(Course::class, 'course_name')
                        ->chunk(20),
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

            Layout::table('applications', [
                TD::make('registration_id', 'Reg. ID'),
                TD::make('institution_name', 'Institution')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Program'),
                TD::make('semester', 'Semester'),
                TD::make('start_date', 'Start Date'),
                TD::make('end_date', 'End Date'),
                TD::make('pending', 'Pending')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 0,
                    ])->count('id');
                }),
                TD::make('approved', 'Approved')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 1
                    ])->count('id');
                }),
                TD::make('rejected', 'Rejected')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 2
                    ])->count('id');
                }),

                TD::make('actions', 'Actions')->render(fn($data) => Link::make('Details')
                    ->class('btn btn-primary btn-sm link-primary')
                    ->route('platform.registration.exam.approve.details', [
                        'registration_period_id' => $this->activePeriod,
                        'registration_id' => $data->registration_id,
                        'institution_id' => $data->institution_id,
                        'course_id' => $data->course_id,
                        'year_of_study' => $data->year_of_study,
                        'trial' => $data->trial
                    ]))

            ])
        ];
    }

    public function filter(Request $request)
    {
        $institutionName = $request->input('institution_name');
        $courseName = $request->input('course_name');


        $filterParams = [];

        if (!empty($institutionName)) {
            $filterParams['filter[institution_name]'] = $institutionName;
        }

        if (!empty($courseName)) {
            $filterParams['filter[course_name]'] = $courseName;
        }

        $url = route('platform.registration.exam.approve', $filterParams);

        return redirect()->to($url);

    }

    public function reset(Request $request)
    {
        $url = route('platform.registration.exam.approve');

        return redirect()->to($url);
    }
}
