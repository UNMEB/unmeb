<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Student;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class IncompleteExamRegistrationDetails extends Screen
{
    public $registration;

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
        $nsinRegistrationId = $data['registration_id'];

        $students = Student::select(
            'students.id',
            'institutions.institution_name',
            'courses.course_name',
            'registrations.id AS registration_id',
            'students.*'
        )
            ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->where('registrations.id', $nsinRegistrationId)
            ->where('courses.id', $courseId)
            ->where('student_registrations.sr_flag', 0);


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
        return 'Incomplete Exam Registrations';
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
