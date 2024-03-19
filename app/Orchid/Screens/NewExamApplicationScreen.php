<?php

namespace App\Orchid\Screens;

use App\Models\Student;
use App\Orchid\Layouts\RegisterStudentsForExamsTable;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class NewExamApplicationScreen extends Screen
{
    public $institutionId;
    public $exam_registration_period_id;
    public $courseId;
    public $paperIds;
    public $yearOfStudy;
    public $trial;

    public function __construct(Request $request)
    {
        $this->institutionId = $request->get('institution_id');
        $this->exam_registration_period_id = $request->get('exam_registration_period_id');
        $this->courseId = $request->get('course_id');
        $this->paperIds = $request->get('paper_ids');
        $this->yearOfStudy = $request->get('year_of_study');
        $this->trial = $request->get('trial');

        session()->put('institution_id', $this->institutionId);
        session()->put('exam_registration_period_id', $this->exam_registration_period_id);
        session()->put('course_id', $this->courseId);
        session()->put('paper_ids', $this->paperIds);
        session()->put('year_of_study', $this->yearOfStudy);
        session()->put('trial', $this->trial);
    }


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $institutionId = session()->get('institution_id');
        $courseId = session()->get('course_id');

        $query = Student::leftJoin('student_registrations as sr', 'students.id', '=', 'sr.student_id')
            ->leftJoin('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->leftJoin('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->leftJoin('courses as c', 'r.course_id', '=', 'c.id')
            ->leftJoin('institutions as i', 'r.institution_id', '=', 'i.id')
            ->where('rp.id', '=', $this->exam_registration_period_id)
            ->where('c.id', '=', $courseId)
            ->where('i.id', '=', $institutionId)
            ->select('students.*')
            ->limit(100)
            ->orderBy('surname', 'asc')
            ->paginate(100);

        return [
            'students' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Register For Exams';
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
            RegisterStudentsForExamsTable::class,
        ];
    }

    public function submit(Request $request)
    {
        dd($request->all());
    }
}
