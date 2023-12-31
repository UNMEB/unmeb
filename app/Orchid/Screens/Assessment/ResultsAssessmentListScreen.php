<?php

namespace App\Orchid\Screens\Assessment;

use Orchid\Screen\Screen;
use App\Models\AssessmentResult;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ResultsAssessmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = AssessmentResult::with('student', 'practicalAssessment', 'theoryAssessment');
        return [
            'results' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Assessment Results';
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
            Layout::table('results', [
                TD::make('id', 'ID'),
                TD::make('student.fullName', 'Student'),
                TD::make('paper.paper_name', 'Paper'),

            ])
        ];
    }
}
