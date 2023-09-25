<?php

namespace App\Orchid\Screens\Administration\Years;

use App\Exports\YearsExport;
use App\Imports\YearsImport;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Attachment\File;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;

class YearListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'years' => Year::paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Years';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Year')
                ->modal('createYearModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Years')
                ->modal('uploadYearsModal')
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
            Layout::table('years', [
                TD::make('id', 'ID')
                    ->width('100'),
                TD::make('name', _('Year Name')),

                TD::make('flag', _('Year Flag'))
                    ->render(function ($year) {
                        if ($year->flag === 1) {
                            return __('Active'); // You can replace 'Yes' with your custom label
                        } else {
                            return __('Inactive'); // You can replace 'No' with your custom label
                        }
                    }),

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
                    ->render(function (Year $year) {
                        $editButton = ModalToggle::make('Edit Year')
                            ->modal('editYearModal')
                            ->modalTitle('Edit Year ' . $year->name)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'year' => $year->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this year?')
                            ->method('delete', [
                                'id' => $year->id
                            ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })
            ]),
            Layout::modal('createYearModal', Layout::rows([
                Input::make('year.name')
                    ->title('Year Name')
                    ->placeholder('Enter year name')
                    ->help('The name of the year e.g 2012')
            ]))
                ->title('Create Year')
                ->applyButton('Create Year'),

            Layout::modal('editYearModal', Layout::rows([
                Input::make('year.name')
                    ->type('text')
                    ->title('Year Name')
                    ->help('Year e.g 2012')
                    ->horizontal(),

                Select::make('year.flag')
                    ->options([
                        1  => 'Active',
                        0  => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive year flag')
                    ->horizontal()
                    ->empty('No select')
            ]))->async('asyncGetYear'),

            Layout::modal('uploadYearsModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Years'),
            ]))
                ->title('Upload Years')
                ->applyButton('Upload Years'),

        ];
    }

    /**
     * @return array
     */
    public function asyncGetYear(Year $year): iterable
    {
        return [
            'year' => $year,
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
            'year.name' => 'required|numeric'
        ]);

        $year = new Year();
        $year->name = $request->input('year.name');
        $year->save();

        Alert::success("Year was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Year $year): void
    {
        $request->validate([
            'year.name'
        ]);

        $year->fill($request->input('year'))->save();

        Alert::info(__('Year was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Year::findOrFail($request->get('id'))->delete();

        Alert::success("Year was deleted.");
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
            Excel::import(new YearsImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Year data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Years data imported successfully.');
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
        return Excel::download(new YearsExport, 'years.csv', ExcelExcel::CSV);
    }
}
