<?php

namespace App\Orchid\Screens;

use App\Exports\NSINApplicationExport;
use App\Models\District;
use App\Models\NsinRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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

    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {

        $this->filters = $request->get("filter");
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $this->nsinRegistrationId = $request->get('nsin_registration_id');

        session()->put('course_id', $courseId);
        session()->put('institution_id', $institutionId);
        session()->put('nsin_registration_id', $request->get('nsin_registration_id'));
        
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
            's.lin',
            's.passport'
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
        $query->orderBy('s.nsin', 'asc');

        if (!empty($this->filters)) {
            
            if (isset($this->filters['district_id']) && $this->filters['district_id'] !== null) {
                $districtId = $this->filters['district_id'];
                $query->where('s.district_id', '=', $districtId);
            }

            if (isset($this->filters['name']) && $this->filters['name'] !== null) {
                $name = $this->filters['name'];
                $terms = explode(' ', $name);
                if (count($terms) == 2) {
                    [$firstTerm, $secondTerm] = $terms;
                    $query->where(function ($query) use ($firstTerm, $secondTerm) {
                        $query->where('firstname', 'like', '%' . $firstTerm . '%')
                        ->where('surname', 'like', '%' . $secondTerm . '%');
                    })->orWhere(function ($query) use ($firstTerm, $secondTerm) {
                        // Check if the first term matches the surname and the second term matches the firstname
                        $query->where('surname', 'like', '%' . $firstTerm . '%')
                            ->where('firstname', 'like', '%' . $secondTerm . '%');
                    });
                }   

                $query->where('firstname', 'like', '%' . $name . '%')
                        ->orWhere('surname', 'like', '%' . $name . '%')
                        ->orWhere('othername', 'like', '%' . $name . '%');
            }

            if (isset($this->filters['gender']) && $this->filters['gender'] !== null) {
                $gender = $this->filters['gender'];
                $query->where('s.gender', '=', $gender);
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
            ->class('btn btn-success')
            ->method('export')
            ->rawClick(),
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

    public function filter(Request $request)
    {
        dd($request->query());

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

        $url = route('platform.registration.nsin.applications.details', $filterParams);

        return redirect()->to($url);
    }

    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.nsin.applications.details');
    }

    public function export(Request $request)
    {
        $nsin_registration_id = session()->get('nsin_registration_id');
        $institutionId = session()->get('institution_id');
        $courseId = session()->get('course_id');

        $students = Student::withoutGlobalScopes()
        ->select([
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            'd.district_name as district',
            'c.nicename as country',
            's.nsin as nsin',
            's.telephone',
            's.passport',
            's.passport_number',
            's.lin',
            's.email'
        ])
        ->from('students as s')
        ->join('nsin_student_registrations as nsr', 's.id', '=','nsr.student_id')
        ->join('nsin_registrations as nr','nsr.nsin_registration_id','=','nr.id')
        ->join('countries AS c', 'c.id','=','s.country_id')
        ->join('districts as d', 'd.id','=','s.district_id')
        ->where('nr.institution_id', $institutionId)
        ->where('nr.course_id', $courseId)
        ->where('nr.id', $nsin_registration_id)
        ->get();

        return Excel::download(new NSINApplicationExport($students), 'nsin_applications.xlsx');

        
    }
}
