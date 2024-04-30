<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use DB;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRegistrationListScreen extends Screen
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
            $this->activePeriod = RegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        if(!is_null($queryPeriod)) {
            $this->activePeriod = $queryPeriod;
        }

        $query = Registration::from('registrations AS r')
        ->join('student_registrations AS sr', 'sr.registration_id', '=', 'r.id')
        ->join('institutions AS i', 'r.institution_id', '=', 'i.id')
        ->join('courses AS c', 'r.course_id', '=', 'c.id')
        ->join('registration_periods AS rp', 'r.registration_period_id', '=', 'rp.id')
        ->select(
            'rp.reg_start_date AS start_date',
            'rp.reg_end_date AS end_date',
            'i.institution_name',
            'c.course_name',
            'rp.academic_year',
            DB::raw('COUNT(CASE WHEN sr.sr_flag = 1 THEN 1 ELSE NULL END) AS approved_count'),
            DB::raw('COUNT(CASE WHEN sr.sr_flag = 2 THEN 1 ELSE NULL END) AS rejected_count'),
            DB::raw('COUNT(CASE WHEN sr.sr_flag = 0 THEN 1 ELSE NULL END) AS pending_count'),
            DB::raw('COUNT(*) AS total_registered')
        )
        ->where('rp.id', $this->activePeriod) // Add condition to filter by activePeriod
        ->groupBy('i.institution_name', 'c.course_name', 'rp.reg_start_date', 'rp.reg_end_date', 'rp.academic_year');
        
        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }
        
        return [
            'registrations' => $query->paginate(10),
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
            $period = RegistrationPeriod::select('*')
                    ->where('id', $this->activePeriod)
                    ->first();

            return 'Exam registrations for ' . $period->reg_start_date->format('Y-m-d') . ' / '. $period->reg_end_date->format('Y-m-d');
        }

        return 'Exam Registrations';
    }

    public function description(): ?string
    {
        return 'View Exam Registrations, application statuses. Filter Exam Registrations';
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
            Layout::table('registrations', [
                TD::make()->render(fn (Registration $model, object $loop) => $loop->index + 1),
                TD::make('institution_name', 'Institution')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Program'),
                TD::make('semester', 'Semester'),
                TD::make('start_date', 'Start Date'),
                TD::make('end_date', 'End Date'),
                TD::make('pending', 'Pending')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 0
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
                TD::make('actions', 'Actions')->render(
                    fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.exam.registrations.details', [
                            'registration_period_id' => $data->registration_period_id,
                            'registration_id' => $data->registration_id,
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,

                        ])
                )
            ])  
        ];
    }
}
