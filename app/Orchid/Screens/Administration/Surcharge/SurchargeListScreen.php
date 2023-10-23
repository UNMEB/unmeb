<?php

namespace App\Orchid\Screens\Administration\Surcharge;

use App\Exports\SurchargeExport;
use App\Imports\SurchargeImport;
use App\Models\Surcharge;
use App\Models\SurchargeFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class SurchargeListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Surcharge::paginate();

        return [
            'surcharges' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Surcharges';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Surcharge')
                ->modal('createSurchargeModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Surcharges')
                ->modal('uploadSurchargesModal')
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
            Layout::table('surcharges', [
                TD::make('id', 'ID')
                    ->width('100'),

                TD::make('surcharge_name', 'Surcharge Name'),

                TD::make('flag', __('Surcharge Flag'))
                    ->render(function (Surcharge $surcharge) {
                        if ($surcharge->flag === 1) {
                            return __('Active'); // You can replace 'Yes' with your custom label
                        } else {
                            return __('Inactive'); // You can replace 'No' with your custom label
                        }
                    }),

                TD::make('actions', 'Actions')
                    ->width(180)
                    ->alignCenter()
                    ->render(function (Surcharge $surcharge) {
                        return  Group::make([
                            ModalToggle::make('Edit')
                                ->modal('editSurchargeModal')
                                ->modalTitle('Edit Surcharge ')
                                ->method('edit')
                                ->type(Color::LINK)
                                ->asyncParameters([
                                    'surcharge' => $surcharge->id,
                                ]),
                            Button::make('Delete')
                                ->confirm('Are you sure you want to delete this surcharge?')
                                ->method('delete', [
                                    'id' => $surcharge->id
                                ])
                                ->type(Color::LINK)
                        ]);
                    })
            ]),

            Layout::modal('createSurchargeModal', Layout::rows([
                Input::make('surcharge.name')
                    ->title('Surcharge Name')
                    ->placeholder('Enter name of surcharge')
                    ->horizontal(),
            ]))
                ->title('Create Surcharge')
                ->applyButton('Create Surcharge'),

            Layout::modal('editSurchargeModal', Layout::rows([
                Input::make('surcharge.name')
                    ->title('Surcharge Name')
                    ->placeholder('Enter name of surcharge')
                    ->horizontal(),

                Select::make('surcharge.flag')
                    ->options([
                        1  => 'Active',
                        0  => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive surcharge flag')
                    ->horizontal()
                    ->empty('No select')
            ]))
                ->title('Update Surcharge')
                ->applyButton('Update Surcharge')
                ->async('asyncGetSurcharge'),

            Layout::modal('uploadSurchargesModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Surcharges'),
            ]))
                ->title('Upload Surcharges')
                ->applyButton('Upload Surcharges'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetSurcharge(Surcharge $surcharge): iterable
    {
        return [
            'surcharge' => $surcharge,
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
            'surcharge.name' => 'required'
        ]);

        $surcharge = new Surcharge();
        $surcharge->name = $request->input('surcharge.name');
        $surcharge->save();

        Alert::success("Surcharge was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Surcharge $surcharge): void
    {
        $request->validate([
            'surcharge.name'
        ]);

        $surcharge->fill($request->input('surcharge'))->save();

        Alert::success(__('Surcharge was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Surcharge::findOrFail($request->get('id'))->delete();

        Alert::success("Surcharge was deleted.");
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
            Excel::import(new SurchargeImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Surcharge data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Surcharges data imported successfully.');
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
        return Excel::download(new SurchargeExport, 'surcharges.csv', ExcelExcel::CSV);
    }
}
