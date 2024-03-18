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

        session()->put("course_id", $course->id);

        $assignedPapers = $course->papers()->paginate();
        $paperIdsAssigned = $course->papers->pluck('id')->all();
        $papersNotAssigned = Paper::whereNotIn('id', $paperIdsAssigned)->paginate();

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
                            'Paper I',
                            'Paper II',
                            'Paper III',
                            'Paper IV',
                            'Paper V',
                            'Paper VI',
                        ])
                        ->title('Paper')
                        ->placeholder('Select paper'),

                    Select::make('paper_code')
                        ->title('Paper Code')
                        ->options([

                        ]),

                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 2' => 'Year 2 Semester 2',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
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
        $courseId = session()->get('course_id');
        $course = Course::find($courseId);
        $data = $request->all();

        if ($request->has('unassign')) {
            $paperIds = $data['unassign'];
            foreach ($paperIds as $paperId) {
                $paper = Paper::find($paperId);
                if (!$paper) {
                    continue; // Skip to the next iteration
                }

                if ($course && $course->papers()->where('papers.id', $paperId)->exists()) {
                    $course->papers()->detach($paperId);
                    // dd('working');
                }
            }

            \RealRashid\SweetAlert\Facades\Alert::success(__('Paper has been unassigned'));

            return back();

        } else {
            $paperIds = $data['assign'];
            foreach ($paperIds as $paperId) {
                $paper = Paper::find($paperId);

                if (!$paper) {
                    // Handle case where paper is not found
                    continue; // Skip to the next iteration
                }

                // Check if paper belongs to course
                if ($course && $course->papers()->where('papers.id', $paperId)->exists()) {
                    $course->papers()->attach($paperId);
                    // dd('working');
                }
            }
        }
    }

}
