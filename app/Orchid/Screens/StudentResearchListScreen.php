<?php

namespace App\Orchid\Screens;

use App\Models\Research;
use App\Models\StudentResearch;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentResearchListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = StudentResearch::query();
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
        return 'Student Research';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Upload Research')
            ->icon('bs.upload')
            ->class('btn btn-primary')
            ->modalTitle('Upload Student Research')
            ->modal('uploadStudentResearch')
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

            Layout::modal('uploadStudentResearch', Layout::rows([
                Upload::make('research_paper')->title('Research Paper'),
                TextArea::make('research_title')->title('Research Title')->placeholder('Enter the title of the research'),
                Quill::make('research_abstract')->title('Research Abstract')
            ]))
            ->size(Modal::SIZE_LG),

            Layout::table('results',[
                TD::make('id', 'ID'),
                TD::make('student', 'Student Name')->render(fn ($student) => $student->full_name),
                TD::make('research_title', 'Researct Title'),
                TD::make('year', 'Research Year'),
                TD::make('submission_date', 'Submission Date')
            ])
        ];
    }
}
