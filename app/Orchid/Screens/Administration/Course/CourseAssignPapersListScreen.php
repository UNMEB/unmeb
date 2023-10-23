<?php

namespace App\Orchid\Screens\Administration\Course;

use App\Models\Course;
use App\Models\Paper;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
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
        $assignedPapers = $course->papers()->paginate();
        $papersNotAssigned = Paper::whereNotIn('id', $course->papers->pluck('id'))->paginate();
        return [
            'course' => $course,
            'assigned_papers' => $assignedPapers,
            'papers_not_assigned' => $papersNotAssigned,
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
            Layout::tabs([
                'Assigned Papers' =>   Layout::table('assigned_papers', [
                    TD::make('id', 'ID'),
                    TD::make('paper_name', 'Paper Name'),
                    TD::make('paper', 'Paper'),
                    TD::make('abbrev', 'Abbrev'),
                    TD::make('code', 'Paper Code'),
                    TD::make('year_of_study', 'Year of Study'),
                    TD::make('created_at', 'Created At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('updated_at', 'Updated At')
                    ->usingComponent(DateTimeSplit::class),
                ]),
                'Assign Papers' =>  Layout::table('papers_not_assigned', [
                    TD::make('id', 'ID'),
                    TD::make('paper_name', 'Paper Name'),
                    TD::make('paper', 'Paper'),
                    TD::make('abbrev', 'Abbrev'),
                    TD::make('code', 'Paper Code'),
                    TD::make('year_of_study', 'Year of Study'),
                    TD::make('created_at', 'Created At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('updated_at', 'Updated At')
                    ->usingComponent(DateTimeSplit::class),
                    TD::make('action', 'Action')
                    ->alignRight()
                        ->render(function (Paper $paper) {
                            return Button::make('Assign')
                            ->method('assign', [
                                'paper_id' => $paper->id
                            ]);
                        })
                ]),
            ])
        ];
    }

    /**
     * Assign a course to an institution.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request)
    {
        $course = $this->course;
        $course->papers()->attach($request->get('paper_id'));

        Alert::success(__('Paper was assigned.'));

        return back();
    }
}
