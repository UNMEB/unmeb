<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ApplyForNSINsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NsinApplicationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $activeNsinPeriod = NsinRegistrationPeriod::whereFlag(1, true)->first();

        $baseQuery = Student::withoutGlobalScopes()
            ->filters()
            ->select([
                's.id',
                's.surname', 
                's.firstname', 
                's.othername', 
                's.dob', 
                's.gender',
                's.country_id', 
                's.district_id', 
                's.nin', 
                's.passport_number', 
                's.refugee_number',
                's.nsin'
                ])
            ->from('students as s')
            ->join('nsin_student_registrations as nsr', 's.id', '=', 'nsr.student_id')
            ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->whereNotNull('nr.institution_id');

        if(auth()->user()->inRole('institution')) {
            $baseQuery->where('nr.institution_id', auth()->user()->institution_id);
        }

        $baseQuery->orderBy('nsr.created_at', 'asc');

        $pendingStudentsQuery = clone $baseQuery;
        $approvedStudentsQuery = clone $baseQuery;

        return [
            'pending_students' => $pendingStudentsQuery->where('nsr.verify', 0)->paginate(),
            'approved_students' => $approvedStudentsQuery->where('nsr.verify', 1)->paginate(),
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Applications';
    }

    public function description(): ?string
    {
        return 'View NSIN Applications, application statuses. Filter NSIN Applications';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('New NSIN Applications')
                ->modal('newNSINApplicationModal')
                ->modalTitle('Create New NSIN Applications')
                ->class('btn btn-success')
                ->method('applyForNSINs'),

            ModalToggle::make('Export NSIN Applications')
            ->class('btn btn-primary')
            ->modal('exportNSINApplications')
            ->modalTitle('Export NSIN Applications')
            ->method('exportNSINApplications')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $registrationPeriods = NsinRegistrationPeriod::select('nsin_registration_periods.id', 'years.year', 'month')
        ->join('years', 'nsin_registration_periods.year_id', '=', 'years.id')
        ->where('nsin_registration_periods.flag', 1)
        ->get();

        $yearOptions = [];

        foreach ($registrationPeriods as $registrationPeriod) {
            $yearOptions[$registrationPeriod->id] = $registrationPeriod->month . ' - ' . $registrationPeriod->year;
        }
    
        return [
            Layout::modal('newNSINApplicationModal', ApplyForNSINsForm::class)
                ->applyButton('Register for NSINs'),

            Layout::modal('exportNSINApplications', Layout::rows([

                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->placeholder('Select User Institution')
                    ->fromModel(Institution::class, 'institution_name', 'id')
                    ->applyScope('userInstitutions')
                    ->value(auth()->user()->institution_id)
                    // ->disabled(!auth()->user()->hasAccess('platform.internals.all_institutions'))
                    ->required(),

                // Select Nsin Registration Period
                Select::make('nsin_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select Nsin Registration Period'),

                Relation::make('course_id')
                        ->fromModel(Course::class, 'course_name')
                        ->title('Course Name'),


                Select::make('status')
                ->title('Application Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved'
                ])
            ]))
            ->applyButton('Export Data')
            ,

            Layout::rows([

                Group::make([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->chunk(20),

                    Input::make('name')
                        ->title('Filter By Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('Filter By District of origin'),

                    Select::make('gender')
                        ->title('Filter By Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Filter Students')
                        ->method('filter',[
                            'section' => 'pending' 
                        ]),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),

            Layout::tabs([

                'Pending NSINs (Current Period)' => [
                    
                    Layout::table('pending_students', [
                        TD::make('id', 'ID'),
                        TD::make('fullName', 'Name'),
                        TD::make('gender', 'Gender'),
                        TD::make('dob', 'Date of Birth'),
                        TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                        TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
                        TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                        TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin == null ? 'NOT APPROVED' : $student->nsin),
                    ])
                ],
                'Approved NSINs (Current Period)' => Layout::table('approved_students', [
                    TD::make('id', 'ID'),
                    TD::make('fullName', 'Name'),
                    TD::make('gender', 'Gender'),
                    TD::make('dob', 'Date of Birth'),
                    TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                    TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
                    TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                    TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
                ]),
            ])
        ];
    }

    public function applyForNSINs(Request $request)
    {
        session()->forget('institution_id');
        session()->forget('course_id');
        session()->forget('nsin_registration_period_id');

        $institutionId = $request->get('institution_id');
        $nsin_registration_period_id = $request->get('nsin_registration_period_id');
        $courseId = $request->get('course_id');

        $url = route('platform.registration.nsin.applications.new', [
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'nsin_registration_period_id' => $nsin_registration_period_id
        ]);

        return redirect()->to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {

        $institutionId = $request->input('institution_id');
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty($name)) {
            $filterParams['filter[name]'] = $name;
        }

        if (!empty($gender)) {
            $filterParams['filter[gender]'] = $gender;
        }

        if (!empty($district)) {
            $filterParams['filter[district_id]'] = $district;
        }

        $url = route('platform.registration.nsin.applications.list', $filterParams);

        return redirect()->to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.nsin.applications.list');
    }
}
