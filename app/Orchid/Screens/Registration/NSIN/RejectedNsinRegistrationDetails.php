<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Institution;
use App\Models\Student;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

use Illuminate\Support\Str;

class RejectedNsinRegistrationDetails extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $data = request()->all();
        $institutionId = $data['institution_id'];
        $courseId = $data['course_id'];
        $nsinRegistrationId = $data['nsin_registration_id'];

        $students = Student::select(
            'students.id',
            'institutions.institution_name',
            'courses.course_name',
            'nsin_registrations.id AS nsin_registration_id',
            'students.*'
        )
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->where('nsin_registrations.id', $nsinRegistrationId)
            ->where('courses.id', $courseId);

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
        return 'Rejected NSIN Registrations';
    }

    public function description(): ?string
    {
        $data = request()->all();
        $institutionId = $data['institution_id'];
        $institution = Institution::find($institutionId);

        return 'Rejected NSIN registrations for ' . Str::title($institution->institution_name);
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
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
            Layout::table('students', [

                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn (Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district_id', 'District')->render(fn (Student $student) => $student->district->district_name),
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
}
