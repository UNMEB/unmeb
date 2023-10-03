<?php

namespace App\Orchid\Screens\Assessment;

use App\Models\TheoryAssessmentMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TheoryAssessmentList extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $assessments = TheoryAssessmentMark::with('institution', 'course', 'paper',)->paginate();

        return [
            'assessment' => $assessments
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Theory Assessment';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Theory Assessment')
                ->modal('theoryAssessmentModal')
                ->method('save')
                ->modalTitle('Record Theory Assessment Marks')
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
            Layout::table('assessment', [
                TD::make('id', 'ID'),
                TD::make('student_id', 'Student ID'),
                TD::make('course_paper_id', 'Course Paper ID'),
                TD::make('assignment_1', 'Assignment 1'),
                TD::make('assignment_2', 'Assignment 2'),
                TD::make('test_1', 'Test 1'),
                TD::make('test_2', 'Test 2'),
                TD::make('total_assignment_mark', 'Total Assignment Mark'),
                TD::make('total_test_mark', 'Total Test Mark'),
                TD::make('total_mark', 'Total Mark'),
            ]),

        ];
    }

    public function save(Request $request)
    {
        $assessmentMark = new TheoryAssessmentMark();
        $assessmentMark->institution_id = auth()->user()->institution->id;
        $assessmentMark->course_id = $request->input('course_id');
        $assessmentMark->paper_id = $request->input('paper_id');
        $assessmentMark->assignment_1 = $request->input('assignment_1');
        $assessmentMark->assignment_2 = $request->input('assignment_2');
        $assessmentMark->test_1 = $request->input('test_1');
        $assessmentMark->test_2 = $request->input('test_2');
    }
}
