<?php

namespace App\Orchid\Screens\Reports;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRegistrationReportScreen extends Screen
{
    public $filters = [];
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {

        $this->filters = $request->get("filter");


        $query = NsinRegistrationPeriod::select(
            'i.id as institution_id',
            'i.institution_name', 
            'c.id as course_id',
            'c.course_name', 
            'y.year', 
            'rp.month', 
            'rp.id as rp_id', 
            'r.id as r_id')
            ->from('nsin_registration_periods as rp')
            ->join('nsin_registrations AS r', function ($join)  {
                $join->on('rp.month','=','r.month');
                $join->on('rp.year_id','=','r.year_id');
            })
            ->join('nsin_student_registrations AS sr', 'r.id', '=','sr.nsin_registration_id')
            ->join('institutions AS i', 'r.institution_id', '=','i.id')
            ->join('courses AS c', 'r.course_id', '=','c.id')
            ->join('years AS y', 'rp.year_id', '=','y.id');

            if(auth()->user()->inRole('institution')) {
                $query->where('i.id', auth()->user()->institution_id);
            }
            
            $query->groupBy('i.institution_name', 'c.course_name', 'y.year', 'rp.month', 'r.id', 'rp.id');

            
            $query->orderBy('rp.id', 'desc');
            $query->orderBy('institution_name','asc');
            $query->orderBy('course_name', 'asc');

            if (!empty($this->filters)) {

                if (isset($this->filters['registration_period_id']) && $this->filters['registration_period_id'] !== null) {
                    $rpId = $this->filters['registration_period_id'];
                    $query->where('rp.id', '=', $rpId);
                }

                if (isset($this->filters['institution_id']) && $this->filters['institution_id'] !== null) {
                    $institutionId = $this->filters['institution_id'];
                    $query->where('i.id', '=', $institutionId);
                }

                if (isset($this->filters['course_id']) && $this->filters['course_id'] !== null) {
                    $courseId = $this->filters['course_id'];
                    $query->where('c.id', '=', $courseId);
                }
            }
        
        
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
        return 'NSIN Registration Report';
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
        $query = NsinRegistrationPeriod::with('year')
        ->orderBy('id', 'desc')
        ->get();

        $periods = [];

        foreach ($query as $queryItem) {
            $periods[$queryItem->id] = $queryItem->month .'/'. optional($queryItem->year)->year;
        }

        return [
            Layout::rows([
                Group::make([
                    Select::make('registration_period_id')
                    ->title('Filter Registration Periods')
                    ->options($periods)
                    ->value(collect($this->filters)->get('registration_period_id'))
                    ,
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
            Layout::table('registrations', [
                TD::make('r_id', 'Reg ID'),
                TD::make('institution_name', 'Institution Name')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Course Name'),
                TD::make('period', 'Registration Period')->render(fn ($data) => $data->month . '/' . $data->year),
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
            ])
        ];
    }

    public function filter(Request $request)
    {
        $filterParams = [];

        if ($request->has('registration_period_id')) {
            $filterParams['filter[registration_period_id]'] = $request->get('registration_period_id');
        }

        if ($request->has('institution_id')) {
            $filterParams['filter[institution_id]'] = $request->get('institution_id');
        }

        if ($request->has('course_id')) {
            $filterParams['filter[course_id]'] = $request->get('course_id');
        }

        $url = route('platform.reports.nsin_registration', $filterParams);

        return redirect()->to($url);
    }
}
