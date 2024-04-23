<?php

namespace App\Orchid\Screens;

use App\Models\District;
use App\Models\NsinRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINApplicationListDetails extends Screen
{

    public $nsinRegistrationId;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $this->nsinRegistrationId = $request->get('nsin_registration_id');
        
        $query = Student::withoutGlobalScopes();
        $query->select([
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            's.district_id',
            's.country_id',
            's.location',
            's.nsin as nsin',
            's.passport_number',
            's.nin',
            's.telephone',
            's.refugee_number',
            's.lin'
        ]);
        $query->from('students As s');
        $query->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id');
        $query->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id');
        $query->join('courses AS c', 'c.id', '=', 'nr.course_id');
        $query->join('years as y', 'nr.year_id', '=', 'y.id');
        $query->where('nr.institution_id', $institutionId);
        $query->where('nr.course_id', $courseId);
        $query->where('nr.id', $this->nsinRegistrationId);
        $query->where('nsr.verify', 0);
        $query->orderBy('nsr.updated_at', 'desc');

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
        return 'NSIN Application Details';
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        $nsinRegistration = NsinRegistration::find($this->nsinRegistrationId);
        if($nsinRegistration) {
            $year = $nsinRegistration->year->year;
            $institution = $nsinRegistration->institution->institution_name;
            return 'NSIN Applications for '. $institution . 'for the period ' . $nsinRegistration->month . '/' . $year;
        }
        return '';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Export Applications')
            ->icon('bs.receipt')
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
                    Input::make('name')
                        ->title('Filter By Student Name'),

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
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),

            Layout::table('applications', [
                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district.district_name', 'District'),
                TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                TD::make('location', 'Location'),
                TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email')->defaultHidden(),
            ])
        ];
    }
}
