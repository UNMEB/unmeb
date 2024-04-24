<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRegistrationsListScreen extends Screen
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
        $this->filters = $request->get('filter');

        $queryPeriod = $request->query('period');

        if(is_null($queryPeriod)) {
            $this->activePeriod = NsinRegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        $query = NsinStudentRegistration::withoutGlobalScopes()
        ->select(
            'nr.id as registration_id',
            'i.id as institution_id',
            'c.id as course_id',
            'i.institution_name', 
            'c.course_name',
        )
        ->from('students AS s')
        ->join('nsin_student_registrations as nsr', 's.id', '=', 'nsr.student_id')
        ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=','nr.id')
        ->join('nsin_registration_periods as nsp', function ($join) {
            $join->on('nsp.year_id','=','nr.year_id');
        })
        ->join('institutions as i', 'nr.institution_id', '=','i.id')
        ->join('courses as c', 'nr.course_id', '=', 'c.id')
        ->groupBy('nr.id','i.id', 'c.id');

        if(!is_null($queryPeriod)) {
            $this->activePeriod = $queryPeriod;
            $query->where('nsp.id', $queryPeriod);
        }

        $query->orderBy('i.institution_name', 'ASC');

        
        return [
            'applications' => $query->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if(!is_null($this->activePeriod)) {
            $period = NsinRegistrationPeriod::select('nsin_registration_periods.id', 'years.year', 'month')
                    ->join('years', 'nsin_registration_periods.year_id', '=', 'years.id')
                    ->orderBy('year', 'desc')
                    ->where('nsin_registration_periods.id', $this->activePeriod)
                    ->first();

            return 'NSIN Registrations for ' . $period->month . ' '. $period->year;
        }

        return 'NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        // Get all NSIN Registration Periods
        $periods = NsinRegistrationPeriod::select('nsin_registration_periods.id', 'years.year', 'month')
                    ->join('years', 'nsin_registration_periods.year_id', '=', 'years.id')
                    ->orderBy('year', 'desc')
                    ->get();

        $layouts = $periods->map(function ($period) {
            return Link::make($period->month . ' - ' . $period->year)
            ->route('platform.registration.nsin.registrations.list', [
                'period' => $period->id,
            ]);
        });

        return [
            DropDown::make('Change Period')
                ->icon('bs.arrow-down')
                ->list($layouts->toArray())
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
            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Filter Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->chunk(20),

                    Relation::make('course_id')
                        ->title('Filter Course')
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
                TD::make('registration_id', 'Reg ID'),
                TD::make('institution_name', 'Institution Name')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Course Name'),
                TD::make('pending', 'Pending NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->registration_id,
                        'verify' => 0
                    ])->count('id');
                }),
                TD::make('approved', 'Approved NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->registration_id,
                        'verify' => 1
                    ])->count('id');
                }),
                TD::make('rejected', 'Rejected NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->registration_id,
                        'verify' => 2
                    ])->count('id');
                }),
                TD::make('actions', 'Actions')
                ->render(fn ($data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
                ->route('platform.registration.nsin.registrations.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'nsin_registration_id' => $data->registration_id
                ]))
            ])
        ];
    }

    public function details(Request $request)
    {

    }
}
