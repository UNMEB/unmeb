<?php

namespace App\Orchid\Screens\Assessment;

use App\Models\PracticalAssessmentMark;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PracticalAssessmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = PracticalAssessmentMark::query();
        return [
            'marks' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Practical Assessment';
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
            ->modal('importPracticalMarksModal')
            ->modalTitle('Import Practical Assessment Marks'),

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

            Layout::modal('importPracticalMarksModal', Layout::rows([
                Input::make('upload_marks')
                ->title('Upload Practical Assessment Marks')
                ->type('file')
                ->required(true)
                    ->help('Upload a spreadsheet Containing Pratical Assessment Marks'),

                Link::make('Download Practical Assessment Template')
                ->rawClick(false),
            ])),


            Layout::table('marks', [
                TD::make('student.fullName', 'Student '),
                TD::make('paper.paper_name', 'Paper'),
                TD::make('practical_test', 'Practical Test'),
                TD::make('clinical_practice', 'Clinical Practice'),
                TD::make('logbook_assessment', 'Logbook Assessment'),
                TD::make('total_mark', 'Totak Mark'),
            ])
        ];
    }
}
