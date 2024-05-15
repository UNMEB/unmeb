<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\District;
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

class IncompleteExamRegistrationDetails extends Screen
{
    public $registration;
    public $institutionId;
    public $courseId;
    public $registrationId;

    public function __construct(Request $request)
    {
        // $data = $request->all();
        // $this->institutionId = $data['institution_id'] ?? null;
        // $this->courseId = $data['course_id'] ?? null;
        // $this->registrationId = $data['registration_id'] ?? null;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $students = Student::
            filters()->
            select(
                'students.id',
                'institutions.institution_name',
                'courses.course_name',
                'registrations.id AS registration_id',
                'students.*',
                'student_registrations.sr_flag'
            )
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->where('registrations.id', $this->registrationId)
            ->where('courses.id', $this->courseId)
            ->where('institutions.id', $this->institutionId)
            ->where('sr_flag', 0);


        return [
            'students' => $students->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registrations';
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

                    Input::make('name')
                        ->title('Search by Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('District of origin'),

                    Select::make('gender')
                        ->title('Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter', [
                            'institution_id' => $this->institutionId,
                            'course_id' => $this->courseId,
                            'registration_id' => $this->registrationId,
                        ]),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('filter', [
                            'institution_id' => $this->institutionId,
                            'course_id' => $this->courseId,
                            'registration_id' => $this->registrationId,
                        ]),

                ])->autoWidth()
                    ->alignEnd(),
            ])->title("Filter Students"),

            Layout::table('students', [

                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district_id', 'District')->render(fn(Student $student) => $student->district->district_name),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('NSIN', 'NSIN'),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email'),
                TD::make('old', 'Old Student'),
                TD::make('date_time', 'Registration Date'),
            ]),

        ];
    }

    public function filter(Request $request)
    {
        // Get existing query parameters
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $registrationId = $request->input('registration_id');

        // Get new filter parameters
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        // Prepare the filters array with only new filter parameters
        $filters = [];

        if (!empty ($name)) {
            $filters['filter[name]'] = $name;
        }
        if (!empty ($gender)) {
            $filters['filter[gender]'] = $gender;
        }
        if (!empty ($district)) {
            $filters['filter[district_id]'] = $district;
        }

        // Combine existing query parameters with new filters
        $queryParams = array_merge([
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'registration_id' => $registrationId
        ], $filters);

        // Redirect to the same route with updated query parameters
        $url = route('platform.registration.exam.incomplete.details', $queryParams);


        return Redirect::to($url);
    }

    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.exam.incomplete.details');
    }
}
