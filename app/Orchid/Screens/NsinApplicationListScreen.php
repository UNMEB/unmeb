<?php

namespace App\Orchid\Screens;

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

        $query = NsinRegistration::
            withoutGlobalScopes()
            ->select('s.*')
            ->from('nsin_registrations as nr')
            ->join('nsin_student_registrations as nsr', 'nr.id', '=','nsr.nsin_registration_id')
            ->join('students as s', 'nsr.student_id', '=','s.id')
            ->join('nsin_registration_periods as nsp', function ($join) {
                $join->on('nr.year_id', '=', 'nsp.year_id')
                    ->on('nr.month', '=', 'nsp.month');
            });

        if (auth()->user()->inRole('institution')) {
            $query->where('s.institution_id', auth()->user()->institution_id);
        }

        $query->where('nsp.id', '=', $activeNsinPeriod->id);

        $query->orderBy('surname', 'asc');

        // dd($query->toRawSql());

        return [
            'pending_students' => $query->paginate(),
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
                ->method('applyForNSINs')
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
            Layout::modal('newNSINApplicationModal', ApplyForNSINsForm::class)
                ->applyButton('Register for NSINs'),


            Layout::tabs([

                'Pending NSINs (Current Period)' => [
                    Layout::rows([

                    ]),
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
                    TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin == null ? 'NOT APPROVED' : $student->nsin),
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
}
