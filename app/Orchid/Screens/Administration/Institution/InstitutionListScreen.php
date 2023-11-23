<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Exports\InstitutionExport;
use App\Imports\InstitutionImport;
use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\InstitutionCourse;
use App\Orchid\Layouts\Selection\InstitutionFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class InstitutionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $institutions = Institution::filters()
            ->defaultSort('id', 'desc')
            ->paginate();

        return [
            'institutions' => $institutions
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Institutions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Institution')
                ->modal('createInstitutionModal')
                ->method('create')
                ->icon('plus'),

            ModalToggle::make('Import Institutions')
                ->modal('uploadInstitutionsModal')
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

            Layout::rows([
                Group::make([
                    Input::make('institution_name')
                        ->title('Filter By Name'),
                    Input::make('code')
                        ->title('Filter By Code'),
                    Input::make('institution_type')
                        ->title('Filter By Type'),
                    Input::make('institution_location')
                        ->title('Filter By Location'),
                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title('Filter Institutions'),

            Layout::table('institutions', [
                TD::make('id', 'ID')
                    ->width('75'),
                TD::make('short_name', __('Short Code'))
                    ->width(150),

                TD::make('institution_name', __('Name'))
                    ->width(300),

                TD::make('institution_location', __('Location')),

                TD::make('institution_type', __('Type')),

                TD::make('category', __('Category')),

                TD::make('code', __('Code')),
                TD::make('email', __('Email Address')),
                TD::make('phone_no', __('Phone Number')),
                TD::make(__('Actions'))
                    ->alignCenter()
                    ->render(function (Institution $institution) {
                    return Group::make([Link::make('Programs')->class('btn btn-primary btn-sm link-primary')
                            ->route('platform.institutions.assign', $institution->id),
                            ModalToggle::make('Edit')
                                ->icon('fa.edit')
                                ->method('edit')
                                ->modal('editInstitutionModal')
                                ->modalTitle('Edit Institution')
                                ->asyncParameters([
                                    'institution' => $institution->id
                            ])
                            ->class('btn btn-success btn-sm link-success'),
                            Button::make('Delete')
                                ->confirm('Are you sure you want to delete this institution?')
                                ->method('delete', [
                                    'id' => $institution->id
                                ])
                        ->class('btn btn-danger btn-sm link-danger')
                        ]);
                    })


            ]),
            Layout::modal('createInstitutionModal', Layout::rows([
                Input::make('institution.short_name')
                    ->title('Short Name')
                    ->placeholder('Enter institution short name'),

                Input::make('institution.institution_name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Input::make('institution.institution_location')
                    ->title('Location')
                    ->placeholder('Institution Location'),

                Input::make('institution.institution_type')
                    ->title('Institution Type')
                    ->placeholder('Enter institution type'),

                Select::make('institution.category')
                ->title('Category')
                ->placeholder('Enter institution category')
                ->options([
                    'UNMEB' => 'UNMEB',
                    'UAHEB' => 'UAHEB',
                    'UBTEB' => 'UBTEB',
                ])
                ->empty('Non Selected'),

                Input::make('institution.code')
                    ->title('Institution Code')
                    ->placeholder('Enter institution code'),

                Input::make('institution.phone_no')
                    ->title('Institution Phone Number')
                    ->placeholder('Enter institution phone number'),

                Input::make('institution.email')
                ->title('Institution Email Address')
                ->type('email')
                ->placeholder('Enter institution email'),

                Input::make('institution.box_no')
                    ->title('Institution P.O.Box')
                    ->placeholder('Enter P.O.Box')
            ]))
                ->title('Create Institution')
                ->applyButton('Create Institution'),

            Layout::modal('editInstitutionModal', Layout::rows([
                Input::make('institution.short_name')
                    ->title('Short Name')
                    ->placeholder('Enter institution short name'),

                Input::make('institution.institution_name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Input::make('institution.institution_location')
                    ->title('Location')
                    ->placeholder('Institution Location'),

                Input::make('institution.institution_type')
                    ->title('Institution Type')
                    ->placeholder('Enter institution type'),

                Select::make('institution.category')
                ->title('Category')
                ->placeholder('Enter institution category')
                ->options([
                    'UNMEB' => 'UNMEB',
                    'UAHEB' => 'UAHEB',
                    'UBTEB' => 'UBTEB',
                ])
                ->empty('Non Selected'),

                Input::make('institution.code')
                    ->title('Institution Code')
                    ->placeholder('Enter institution code'),

                Input::make('institution.phone_no')
                    ->title('Institution Phone Number')
                    ->placeholder('Enter institution phone number'),

                Input::make('institution.email')
                ->title('Institution Email Address')
                ->type('email')
                ->placeholder('Enter institution email'),

                Input::make('institution.box_no')
                    ->title('Institution P.O.Box')
                    ->placeholder('Enter P.O.Box')

            ]))->async('asyncGetInstitution'),

            Layout::modal('uploadInstitutionsModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Institutions'),
            ]))
                ->title('Upload Institutions')
                ->applyButton('Upload Institutions'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetInstitution(Institution $institution): iterable
    {
        return [
            'institution' => $institution,
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
            'institution.institution_name' => 'required',
            'institution.short_name' => 'required',
            'institution.institution_location' => 'required',
            'institution.institution_type' => 'required',
            'institution.code' => 'required',
            'institution.phone_no' => 'required',
            'institution.box_no' => 'required',
            'institution.email' => 'required',
        ]);

        $institution = new Institution();
        $institution->institution_name = $request->input('institution.institution_name');
        $institution->short_name = $request->input('institution.short_name');
        $institution->institution_location = $request->input('institution.institution_location');
        $institution->institution_type = $request->input('institution.institution_type');
        $institution->code = $request->input('institution.code');
        $institution->phone_no = $request->input('institution.phone_no');
        $institution->box_no = $request->input('institution.box_no');
        $institution->email = $request->input('institution.email');
        $institution->category = $request->input('institution.category');
        $institution->save();

        Alert::success("Institution was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Institution $institution): void
    {
        $request->validate([
            'institution.institution_name' => 'required',
            'institution.short_name' => 'required',
            'institution.institution_location' => 'required',
            'institution.institution_type' => 'required',
            'institution.code' => 'required',
            'institution.phone_no' => 'required',
            'institution.email' => 'required',
            'institution.box_no' => 'required',
        ]);

        $institution->fill($request->input('institution'))->save();

        Alert::success(__('Institution was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Institution::findOrFail($request->get('id'))->delete();

        Alert::success("Institution was deleted.");
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
            Excel::import(new InstitutionImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Institution data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Institutions data imported successfully.');
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
        // return Excel::download(new InstitutionExport, 'institutions.csv', ExcelExcel::CSV);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function assign(Request $request)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {
        // Retrieve data from the request
        $institutionName = $request->input('institution_name');
        $institutionCode = $request->input('code');
        $institutionType = $request->input('institution_type');
        $institutionLocation = $request->input('institution_location');

        // Define the filter parameters
        $filterParams = [];

        // Check and add each parameter to the filterParams array
        if (!empty($institutionName)) {
            $filterParams['filter[institution_name]'] = $institutionName;
        }
        if (!empty($institutionCode)) {
            $filterParams['filter[code]'] = $institutionCode;
        }
        if (!empty($institutionType)) {
            $filterParams['filter[institution_type]'] = $institutionType;
        }
        if (!empty($institutionLocation)) {
            $filterParams['filter[institution_location]'] = $institutionLocation;
        }

        // Generate the URL with the filter parameters using the "institutions" route
        $url = route('platform.institutions', $filterParams);

        // Redirect to the generated URL
        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.institutions');
    }
}
