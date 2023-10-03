<?php

namespace App\Orchid\Screens\Administration\Course;

use App\Models\Course;
use App\Models\Paper;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AssignPaperListScreen extends Screen
{

    public $course;


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Course $course): iterable
    {
        // Retrieve all paper IDs assigned to the course
        $assignedPaperIds = Course::find($course->id)->papers()->pluck('papers.id')->toArray();

        // Retrieve papers that are not assigned to the course
        $unassignedPapers = Paper::whereNotIn('id', $assignedPaperIds)->get();

        return [
            'course' => $course,
            'papers' => $unassignedPapers
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assign papers to ' . $this->course->name;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Back to Courses')->route('platform.systems.administration.courses')
                ->icon('bs.arrow-left')
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
            Layout::table('papers', [
                TD::make('id', 'ID'),
                TD::make('abbrev', 'Abbreviation'),
                TD::make('code', 'Paper Code'),
                TD::make('name', 'Paper Name'),
                TD::make('paper', 'Paper'),
                TD::make('study_period', 'Study Period'),
                TD::make('actions', 'Actions')
                    ->alignRight()
                    ->render(function ($data) {
                        return Button::make('Assign Paper')->method('assign', [
                            'id' => $data->id
                        ]);
                    })
            ])
        ];
    }

    public function assign(Request $request)
    {
        // Retrieve the paper ID from the request
        $paperId = $request->input('id');

        // Check if the paper ID is valid
        $paper = Paper::find($paperId);

        if (!$paper) {
            Alert::error('Paper not found.');
            return redirect()->back();
        }

        // Attach the paper to the course (assuming a many-to-many relationship)
        $this->course->papers()->attach($paperId);

        Alert::success('Paper assigned successfully.');
        return redirect()->back();
    }
}
