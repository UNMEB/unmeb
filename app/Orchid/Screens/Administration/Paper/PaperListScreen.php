<?php

namespace App\Orchid\Screens\Administration\Paper;


use App\Exports\PaperExport;
use App\Imports\PaperImport;
use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PaperListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $papers = Paper::paginate();
        return [
            'papers' => $papers
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Papers';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Paper')
                ->modal('createPaperModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Papers')
                ->modal('uploadPapersModal')
                ->method('upload')
                ->icon('upload'),
            Button::make('Export Data')
                ->method('download')
                ->rawClick(false)
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
                TD::make('id', 'ID')
                    ->width('75'),
                TD::make('code', _('Paper Code')),

                TD::make('name', _('Paper Name')),

                TD::make('paper', _('Paper')),

                TD::make('abbrev', _('Abbreviation')),

                TD::make('year', _('Year')),

                TD::make('created_at', __('Created On'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make(__('Actions'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (Paper $paper) {
                        $editButton = ModalToggle::make('Edit Paper')
                            ->modal('editPaperModal')
                            ->modalTitle('Edit Paper ' . $paper->name)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'paper' => $paper->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this paper?')
                            ->method('delete', [
                                'id' => $paper->id
                            ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })


            ]),
            Layout::modal('createPaperModal', Layout::rows([

                Input::make('paper.name')
                    ->title('Paper Name')
                    ->placeholder('Enter paper name'),

                Input::make('paper.year')
                    ->title('Year of Study')
                    ->placeholder('Enter year of study'),

                Select::make('paper.paper')
                    ->options([
                        'Paper I',
                        'Paper II',
                        'Paper III',
                        'Paper IV',
                        'Paper V',
                    ])
                    ->title('Paper')
                    ->placeholder('Select paper'),

                Input::make('paper.abbrev')
                    ->title('Year of Study')
                    ->placeholder('Enter abbreviation'),

                Input::make('paper.code')
                    ->title('Paper Code')
                    ->placeholder('Enter paper code'),

            ]))
                ->title('Create Paper')
                ->applyButton('Create Paper'),

            Layout::modal('editPaperModal', Layout::rows([
                Input::make('paper.name')
                    ->title('Paper Name')
                    ->placeholder('Enter paper name'),

                Input::make('paper.year')
                    ->title('Year of Study')
                    ->placeholder('Enter year of study'),

                Select::make('paper.paper')
                    ->options([
                        'Paper I',
                        'Paper II',
                        'Paper III',
                        'Paper IV',
                        'Paper V',
                        'Paper VI'
                    ])
                    ->title('Paper')
                    ->placeholder('Select paper'),

                Input::make('paper.abbrev')
                    ->title('Year of Study')
                    ->placeholder('Enter abbreviation'),

                Input::make('paper.code')
                    ->title('Paper Code')
                    ->placeholder('Enter paper code'),
            ]))->async('asyncGetPaper'),

            Layout::modal('uploadPapersModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Papers'),
            ]))
                ->title('Upload Papers')
                ->applyButton('Upload Papers'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetPaper(Paper $paper): iterable
    {
        return [
            'paper' => $paper,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate([
            'paper.name' => 'required',
            'paper.code' => 'required',
            'paper.year' => 'required',
            'paper.paper' => 'required',
            'paper.abbrev' => 'required',
        ]);

        $paper = new Paper();
        $paper->name = $request->input('paper.name');
        $paper->code = $request->input('paper.code');
        $paper->year = $request->input('paper.year');
        $paper->paper = $request->input('paper.paper');
        $paper->abbrev = $request->input('paper.abbrev');
        $paper->save();

        Alert::success("Paper was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Paper $paper): void
    {
        $request->validate([
            'paper.name' => 'required',
            'paper.code' => 'required',
            'paper.year' => 'required',
            'paper.paper' => 'required',
            'paper.abbrev' => 'required',
        ]);

        $paper->fill($request->input('paper'))->save();

        Alert::success(__('Paper was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Paper::findOrFail($request->get('id'))->delete();

        Alert::success("Paper was deleted.");
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function upload(Request $request)
    {
        // Define custom error messages for validation
        $customMessages = [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be a CSV file.',
            'file.max' => 'The file size must not exceed 64MB.',
        ];

        // Validate the request data using the defined rules and custom messages
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv|max:64000', // 64MB in kilobytes
            // Add any other validation rules you need for other fields
        ], $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the uploaded file from the request
        $uploadedFile = $request->file('file');

        // Use Laravel Excel to import the data using your custom importer
        try {
            // Get the path of the uploaded file
            $filePath = $uploadedFile->path();

            // Import the data using your custom importer
            Excel::import(new PaperImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Paper data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Papers data imported successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during import
            Alert::error($e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function download(Request $request)
    {
        return Excel::download(new PaperExport, 'papers.csv', ExcelExcel::CSV);
    }
}
