<?php

namespace App\Orchid\Screens\Administration\Institution;

use App\Exports\InstitutionExport;
use App\Imports\InstitutionImport;
use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\InstitutionCourse;
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

class InstitutionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $institutions = Institution::with('district')
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

            ModalToggle::make('Assign course')
                ->icon('plus')
                ->method('assign')
                ->modal('assignCourse'),

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
            Layout::table('institutions', [
                TD::make('id', 'ID')
                    ->width('75'),
                TD::make('short_name', _('Short Code'))
                    ->width(150),

                TD::make('name', _('Name'))
                    ->width(300),

                TD::make('district', _('District'))->render(function (Institution $institution) {
                    return '<p>' . $institution->district->name . '</p>';
                }),

                TD::make('type', _('Type')),

                TD::make('code', _('Code')),

                TD::make('phone', _('Phone Number')),

                // TD::make('created_at', __('Created On'))
                //     ->usingComponent(DateTimeSplit::class)
                //     ->align(TD::ALIGN_RIGHT)
                //     ->sort(),

                // TD::make('updated_at', __('Last Updated'))
                //     ->usingComponent(DateTimeSplit::class)
                //     ->align(TD::ALIGN_RIGHT)
                //     ->sort(),

                TD::make(__('Assign'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (Institution $institution) => Link::make('Assign Courses')
                        ->route('platform.administration.institutions.assign', $institution->id)),


            ]),
            Layout::modal('createInstitutionModal', Layout::rows([
                Input::make('institution.short_name')
                    ->title('Short Name')
                    ->placeholder('Enter institution short name'),

                Input::make('institution.name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Select::make('institution.district')
                    ->title('District')
                    ->fromModel(District::class, 'name'),

                Input::make('institution.name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Input::make('institution.name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Input::make('institution.name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name'),

                Input::make('institution.name')
                    ->title('Institution Name')
                    ->placeholder('Enter institution name')


            ]))
                ->title('Create Institution')
                ->applyButton('Create Institution'),

            Layout::modal('editInstitutionModal', Layout::rows([
                Input::make('institution.name')
                    ->type('text')
                    ->title('Institution Name')
                    ->help('Institution e.g 2012')
                    ->horizontal(),

                Select::make('institution.flag')
                    ->options([
                        1  => 'Active',
                        0  => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive institution flag')
                    ->horizontal()
                    ->empty('No select')
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
            'institution.name' => 'required',
            'institution.short_name' => 'required',
            'institution.location' => 'required',
            'institution.type' => 'required',
            'institution.code' => 'required',
            'institution.phone' => 'required',
            'institution.box_no' => 'required',
        ]);

        $institution = new Institution();
        $institution->name = $request->input('institution.name');
        $institution->short_name = $request->input('institution.short_name');
        $institution->district_id = $request->input('institution.district_id');
        $institution->type = $request->input('institution.type');
        $institution->code = $request->input('institution.code');
        $institution->phone = $request->input('institution.phone');
        $institution->box_no = $request->input('institution.box_no');
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
            'institution.name'
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
        return Excel::download(new InstitutionExport, 'institutions.csv', ExcelExcel::CSV);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function assign(Request $request)
    {
    }
}
