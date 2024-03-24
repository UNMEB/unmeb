<?php

namespace App\Orchid\Screens;

use App\Models\Institution;
use App\Models\NsinRegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ApplyForNSINsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Relation;
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

        $pendingQuery = Student::query()
            ->join('nsin_student_registrations as nsr', 'students.id', '=', 'nsr.student_id')
            ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->join('nsin_registration_periods as nsp', function ($join) {
                $join->on('nr.year_id', '=', 'nsp.year_id')
                    ->on('nr.month', '=', 'nsp.month');
            })
            ->whereNotNull('nsp.id')
            ->where('nsp.id', '=', $activeNsinPeriod->id)
            ->where('nsr.verify', 0)
            ->whereNull('nsr.nsin')
            ->select('students.*')
            ->orderBy('surname', 'asc')
            ->paginate();

        $approvedQuery = Student::query()
            ->join('nsin_student_registrations as nsr', 'students.id', '=', 'nsr.student_id')
            ->join('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->join('nsin_registration_periods as nsp', function ($join) {
                $join->on('nr.year_id', '=', 'nsp.year_id')
                    ->on('nr.month', '=', 'nsp.month');
            })
            ->whereNotNull('nsp.id')
            ->where('nsp.id', '=', $activeNsinPeriod->id)
            ->where('nsr.verify', 1)
            ->select('students.*')
            ->orderBy('surname', 'asc')
            ->paginate();
        return [
            'pending_students' => $pendingQuery,
            'approved_students' => $approvedQuery,
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
                'Pending NSINs (Current Period)' => Layout::table('pending_students', [
                    TD::make('id', 'ID'),
                    TD::make('fullName', 'Name'),
                    TD::make('gender', 'Gender'),
                    TD::make('dob', 'Date of Birth'),
                    TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                    TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
                    TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                    TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin == null ? 'NOT APPROVED' : $student->nsin),
                ]),
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
}
