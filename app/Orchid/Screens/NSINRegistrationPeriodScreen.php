<?php

namespace App\Orchid\Screens;

use App\Exports\YearsExport;
use App\Imports\ExamRegistrationImport;
use App\Imports\NsinRegistrationImport;
use App\Imports\YearsImport;
use App\Models\RegistrationPeriod;
use App\Models\RegistrationPeriodNsin;
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

class NSINRegistrationPeriodScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'nsinPeriods' => RegistrationPeriodNsin::paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Registration Periods';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add NSIN Period')
            ->modal('createRegistrationPeriodModal')
            ->method('create')
            ->icon('plus'),
            ModalToggle::make('Import NSIN Periods')
            ->modal('uploadExamPeriodModal')
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
            Layout::table('nsinPeriods', [
                TD::make('id', 'ID')
                    ->width('100'),
                TD::make('year_id', 'Year')
                ->render(function ($data) {
                    return $data->year->name;
                }),
                TD::make('month', 'Month'),

                TD::make('flag', _('Status Flag'))
                ->render(function ($nsinPeriod) {
                    if ($nsinPeriod->flag === 1) {
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
                    ->render(function (RegistrationPeriodNsin $data) {
                        $editButton = ModalToggle::make('Edit Period')
                        ->modal('editDataModal')
                        ->modalTitle('Edit NSIN Registration Period ')
                        ->method('edit') // You can define your edit method here
                        ->asyncParameters([
                            'nsinPeriod' => $data->id,
                        ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                        ->confirm('Are you sure you want to delete this period?')
                        ->method('delete', [
                            'id' => $data->id
                        ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })
            ]),
            Layout::modal('createRegistrationPeriodModal', Layout::rows([
                Select::make('nsinPeriod.year_id')
                ->fromModel(Year::class, 'name')
                ->title('Year')
                ->placeholder('Select Year')
                ->horizontal(),

                Input::make('nsinPeriod.month')
                ->title('Month')
                ->type('text')
                ->placeholder('Enter month')
                ->horizontal(),
            ]))
                ->title('Add Period')
                ->applyButton('Add Period'),

            Layout::modal('editDataModal', Layout::rows([

                Select::make('nsinPeriod.year_id')
                ->fromModel(Year::class, 'name')
                ->title('Year')
                ->placeholder('Select Year')
                ->horizontal(),

                Input::make('nsinPeriod.month')
                ->title('Month')
                ->type('text')
                ->placeholder('Enter month')
                ->horizontal(),

                Select::make('nsinPeriod.flag')
                ->options([
                    1  => 'Active',
                    0  => 'Inactive',
                ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive flag')
                    ->horizontal()
                    ->empty('No select')
            ]))->async('asyncGetPeriod'),

            Layout::modal('uploadExamPeriodModal', Layout::rows([
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
    public function asyncGetPeriod(RegistrationPeriodNsin $nsinPeriod): iterable
    {
        return [
            'nsinPeriod' => $nsinPeriod,
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
            'nsinPeriod.name' => 'required|numeric'
        ]);

        $nsinPeriod = new RegistrationPeriod();
        $nsinPeriod->name = $request->input('nsinPeriod.name');
        $nsinPeriod->save();

        Alert::success("Year was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, RegistrationPeriod $nsinPeriod): void
    {
        $request->validate([
            'nsinPeriod.academic_year',
            'nsinPeriod.start_date',
            'nsinPeriod.end_date',
            'nsinPeriod.flag'
        ]);

        $nsinPeriod->fill($request->input('nsinPeriod'))->save();

        Alert::info(__('Year was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        RegistrationPeriod::findOrFail($request->get('id'))->delete();

        Alert::success("NSIN period was deleted.");
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
            Excel::import(new NsinRegistrationImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Period data imported successfully");

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
        return Excel::download(new YearsExport, 'nsinPeriods.csv', ExcelExcel::CSV);
    }
}
