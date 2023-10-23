<?php

namespace App\Orchid\Screens\Assessment;

use App\Models\TheoryAssessmentMark;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TheoryAssessmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = TheoryAssessmentMark::query();

        return ['marks' => $query->paginate()];
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
            ModalToggle::make('Import Assessment Marks')
            ->modal('importTheoryMarksModal')
            ->modalTitle('Import Theory Assessment Marks'),

            Button::make('Export Data'),

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
            Layout::modal('importTheoryMarksModal', Layout::rows([
                Input::make('upload_marks')
                ->title('Upload Theory Assessment Marks')
                ->type('file')
                ->required(true)
                    ->help('Upload a spreadsheet Containing Theory Assessment Marks'),

                Link::make('Download Theory Assessment Template')
                ->rawClick(false),
            ])),

            Layout::table('theory_list', [
                TD::make('id', 'ID'),
                TD::make('student.fullName', 'Student'),
                TD::make('paper.paper_name', 'Paper'),
                TD::make('Assignment 1'),
                TD::make('Assignment 2'),
                TD::make('Assignment Total'),
                TD::make('Test 1'),
                TD::make('Test 2'),
                TD::make('Test Total'),
                TD::make('Total Mark'),
            ])
        ];
    }
}
