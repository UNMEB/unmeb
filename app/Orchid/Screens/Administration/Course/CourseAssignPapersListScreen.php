<?php

namespace App\Orchid\Screens\Administration\Course;

use App\Models\Course;
use App\Models\Paper;
use App\Orchid\Layouts\FormAssignPapers;
use App\Orchid\Layouts\FormUnAssignPapers;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CourseAssignPapersListScreen extends Screen
{
    /**
     * @var Course
     */
    public $course;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Course $course): iterable
    {
        session()->remove("course_id");

        session()->put("course_id", $course->id);

        $assignedPapers = $course->papers()->paginate();
        $paperIdsAssigned = $course->papers->pluck('id')->all();
        $papersNotAssigned = Paper::whereNotIn('id', $paperIdsAssigned)
            ->where('code', 'LIKE', "$course->course_code%")->paginate();

        return [
            'course' => $course,
            'assigned_papers' => $assignedPapers,
            'unassigned_papers' => $papersNotAssigned,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assign Papers';
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
                    Input::make('paper_name')
                        ->title('Paper Name'),

                    Select::make('paper')
                        ->options([
                            'Paper I' => 'Paper I',
                            'Paper II' => 'Paper II',
                            'Paper III' => 'Paper III',
                            'Paper IV' => 'Paper IV',
                            'Paper V' => 'Paper V',
                            'Paper VI' => 'Paper VI',
                        ])
                        ->title('Paper')
                        ->placeholder('Select paper')
                        ->empty('None Selected'),

                    Input::make('code')
                        ->title('Paper Code'),

                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
                            'Year 3 Semester 2' => 'Year 3 Semester 2',
                        ])

                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title('Filter Papers'),
            FormAssignPapers::class
        ];
    }

    public function submit(Request $request)
    {
        $courseId = session('course_id');
        $course = Course::find($courseId);
        $data = $request->all();


        $paperIds = $data['assign'];
        foreach ($paperIds as $paperId) {
            $paper = Paper::find($paperId);

            if (!$paper) {
                // Handle case where paper is not found
                continue; // Skip to the next iteration
            }

            // Check if paper belongs to course
            if ($course && !$course->papers()->where('papers.id', $paperId)->exists()) {
                $course->papers()->attach($paperId);
            }
        }

    }

    public function filter(Request $request)
    {
        $paperName = $request->get('paper_name');
        $paper = $request->get('paper');
        $code = $request->get('code');
        $yearOfStudy = $request->get('year_of_study');



        // Define the filter parameters
        $filterParams = [];

        if (!empty($paperName)) {
            $filterParams['filter[paper_name]'] = $paperName;
        }

        if (!empty($paper)) {
            $filterParams['filter[paper]'] = $paper;
        }

        if (!empty($code)) {
            $filterParams['filter[code]'] = $code;
        }

        if (!empty($yearOfStudy)) {
            $filterParams['filter[year_of_study]'] = $yearOfStudy;
        }

        // Add the course parameter to the filterParams
        $filterParams['course'] = $this->course->id;

        $url = route('platform.courses.assign', $filterParams);

        return redirect()->to($url);
    }

}
