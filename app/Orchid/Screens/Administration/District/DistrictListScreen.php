<?php

namespace App\Orchid\Screens\Administration\District;

use App\Exports\DistrictExport;
use App\Imports\DistrictImport;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class DistrictListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'districts' => District::paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Districts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add District')
                ->modal('createDistrictModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Districts')
                ->modal('uploadDistrictsModal')
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
            Layout::table('districts', [
                TD::make('id', 'ID')
                    ->width('100'),

                TD::make('district_name', __('District Name')),

                TD::make('created_at', __('Created On'))
                    ->usingComponent(DateTimeSplit::class)
                    ->width('120')
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->width('120')
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make(__('Actions'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (District $district) {
                        $editButton = ModalToggle::make('Edit District')
                            ->modal('editDistrictModal')
                            ->modalTitle('Edit ' . $district->name)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'district' => $district->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this district?')
                            ->method('delete', [
                                'id' => $district->id
                            ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })
            ]),
            Layout::modal('createDistrictModal', Layout::rows([
                Input::make('district.name')
                    ->title('District Name')
                    ->placeholder('Enter district name')
                    ->help('The name of the district e.g Masaka')
            ]))
                ->title('Create District')
                ->applyButton('Create District'),

            Layout::modal('editDistrictModal', Layout::rows([
                Input::make('district.name')
                    ->type('text')
                    ->title('District Name')
                    ->help('District e.g 2012')
                    ->horizontal(),
            ]))->async('asyncGetDistrict'),

            Layout::modal('uploadDistrictsModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Districts'),
            ]))
                ->title('Upload Districts')
                ->applyButton('Upload Districts'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetDistrict(District $district): iterable
    {
        return [
            'district' => $district,
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
            'district.name' => 'required|numeric'
        ]);

        $district = new District();
        $district->name = $request->input('district.name');
        $district->save();

        Alert::success("District was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, District $district): void
    {
        $request->validate([
            'district.name'
        ]);

        $district->fill($request->input('district'))->save();

        Alert::success(__('District was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        District::findOrFail($request->get('id'))->delete();

        Alert::success("District was deleted.");
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
            Excel::import(new DistrictImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("District data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Districts data imported successfully.');
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
        return Excel::download(new DistrictExport, 'districts.csv', ExcelExcel::CSV);
    }
}
