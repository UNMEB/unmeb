<?php

namespace App\Orchid\Screens;

use App\Exports\NSINRegistrationExport;
use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ExportNSINRegistrationForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
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

        if (is_null($queryPeriod)) {
            $this->activePeriod = NsinRegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        if (!is_null($queryPeriod)) {
            $this->activePeriod = $queryPeriod;
        }

        // dd($this->activePeriod);

        $query = NsinRegistrationPeriod::select(
            'i.id as institution_id',
            'i.institution_name',
            'c.id as course_id',
            'c.course_name',
            'y.year',
            'rp.month',
            'rp.id as rp_id',
            'r.id as r_id'
        )
            ->from('nsin_registration_periods as rp')
            ->join('nsin_registrations AS r', function ($join) {
                $join->on('rp.month', '=', 'r.month');
                $join->on('rp.year_id', '=', 'r.year_id');
            })
            ->join('nsin_student_registrations AS sr', 'r.id', '=', 'sr.nsin_registration_id')
            ->join('institutions AS i', 'r.institution_id', '=', 'i.id')
            ->join('courses AS c', 'r.course_id', '=', 'c.id')
            ->join('years AS y', 'rp.year_id', '=', 'y.id')
            ->where('rp.id', $this->activePeriod);

        if (auth()->user()->inRole('institution')) {
            $query->where('i.id', auth()->user()->institution_id);
        }

        $query->groupBy('i.institution_name', 'c.course_name', 'y.year', 'rp.month', 'r.id', 'rp.id');

        if (!empty($this->filters)) {
            if (isset($this->filters['institution_id']) && $this->filters['institution_id'] !== null) {
                $institutionId = $this->filters['institution_id'];
                $query->where('r.institution_id', '=', $institutionId);
            }
        }

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
        if (!is_null($this->activePeriod)) {
            $period = NsinRegistrationPeriod::select('nsin_registration_periods.id', 'years.year', 'month')
                ->join('years', 'nsin_registration_periods.year_id', '=', 'years.id')
                ->orderBy('year', 'desc')
                ->where('nsin_registration_periods.id', $this->activePeriod)
                ->first();

            return 'NSIN Registrations for ' . $period->month . ' ' . $period->year;
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

            ModalToggle::make('Export NSINs')
                ->modal('exportNSINRegistrations')
                ->modalTitle('Export NSIN Registrations')
                ->method('exportNSINs'),

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
            Layout::modal('exportNSINRegistrations', ExportNSINRegistrationForm::class)
                ->rawClick()
                ->open(false),
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
                TD::make('r_id', 'Reg ID'),
                TD::make('institution_name', 'Institution Name')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Course Name'),
                TD::make('pending', 'Pending NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->r_id,
                        'verify' => 0
                    ])->count('id');
                }),
                TD::make('approved', 'Approved NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->r_id,
                        'verify' => 1
                    ])->count('id');
                }),
                TD::make('rejected', 'Rejected NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where([
                        'nsin_registration_id' => $data->r_id,
                        'verify' => 2
                    ])->count('id');
                }),
                TD::make('invalid', 'Invalid NSINS')->render(function ($data) {
                    return NsinStudentRegistration::where('nsin_registration_id', $data->r_id)
                        ->whereIn('verify', [1, 2])
                        ->where('nsin', 'REGEXP', '/^[A-Z]{3}[0-9]{2}\/[A-Z0-9]{3}\/[A-Z]{3}\/[0-9]{3}$/') // Use REGEXP in the where clause directly
                        ->count('id');
                }),
                TD::make('actions', 'Actions')
                    ->render(fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.nsin.registrations.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'registration_id' => $data->r_id,
                            'registration_period_id' => $data->rp_id,
                        ]))
            ])
        ];
    }

    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');

        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        $url = route('platform.registration.nsin.registrations.list', $filterParams);

        return redirect()->to($url);
    }

    public function exportNSINs(Request $request)
    {
        // dd($request->all());

        $nsinRegistrationPeriodId = $request->input('nsin_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $nsinStatus = $request->input('nsin_status');

        $students = Student::
            select([
                's.id as id',
                's.surname',
                's.firstname',
                's.othername',
                's.gender',
                's.dob',
                's.district_id',
                's.country_id',
                's.nsin as nsin',
                's.telephone',
            ])
            ->from('students AS s')
            ->join('nsin_student_registrations as nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('nsin_registration_periods as nrp', function ($join) {
                $join->on('nr.year_id', '=', 'nrp.year_id')
                    ->on('nr.month', '=', 'nrp.month');
            })
            ->where('nr.institution_id', $institutionId)
            ->where('nr.course_id', $courseId)
            ->where('nrp.flag', 1)
            ->where('nsr.verify', $nsinStatus)
            ->get();

        return Excel::download(new NSINRegistrationExport($students), 'nsin_registrations.xlsx');

    }
}
