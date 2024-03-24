<?php

namespace App\Orchid\Screens;

use App\Models\Research;
use App\Models\Student;
use App\Models\StudentResearch;
use Illuminate\Http\Request;
use Orchid\Attachment\File;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
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
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Upload Research')
                ->icon('bs.upload')
                ->class('btn btn-primary')
                ->modalTitle('Upload Student Research')
                ->modal('uploadStudentResearch')
                ->method('submit')
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
                Relation::make('student.student_id')
                    ->fromModel(Student::class, 'id')
                    ->title('Select students to register for Exams')
                    ->displayAppend('studentWithNin')
                    ->searchColumns('surname', 'othername', 'firstname')
                    ->placeholder('Select Student')
                    ->chunk(100),

                TextArea::make('student.research_title')->title('Research Title')->placeholder('Enter the title of the research'),
                Quill::make('student.research_abstract')->title('Research Abstract'),

                Input::make('student.file')
                    ->title('Upload Research Document')
                    ->type('file')
            ]))
                ->size(Modal::SIZE_LG),

            Layout::table('results', [
                TD::make('id', 'ID'),
                TD::make('student', 'Student Name')->render(fn($student) => $student->full_name),
                TD::make('research_title', 'Researct Title'),
                TD::make('year', 'Research Year'),
                TD::make('submission_date', 'Submission Date')
            ])
        ];
    }

    public function submit(Request $request)
    {
        $studentId = $request->input('student.student_id');
        $researchTitle = $request->input('student.research_title');
        $researchAbstract = $request->input('student.research_abstract');

        $filePath = $request->file('student.file');
        $file = new File($filePath);
        $attachment = $file->path("research_documents/" . $studentId)->load();
        $researchLink = $attachment->url();
        $submissionDate = now();
        $year = $request->input('student.year');

        $student = Student::find($studentId);

        if (!$student) {
            Alert::error("Student Record not found");

            return back()->with("error", "Student Record not found")->withInput($request->all());
        }

        $research = new StudentResearch();
        $research->student_id = $studentId;
        $research->research_title = $researchTitle;
        $research->research_abstract = $researchAbstract;
        $research->submission_date = $submissionDate;
        $research->submitted_by = auth()->user()->id;
        $research->research_link = $researchLink;

        $research->save();

        \RealRashid\SweetAlert\Facades\Alert::success("Research Uploaded", "The research document has been uploaded");

        return back();
    }
}
