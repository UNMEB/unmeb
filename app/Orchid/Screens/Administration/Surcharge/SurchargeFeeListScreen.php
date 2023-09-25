<?php

namespace App\Orchid\Screens\Administration\Surcharge;

use App\Imports\SurchargeFeeImport;
use App\Models\SurchargeFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class SurchargeFeeListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = SurchargeFee::with(['course', 'surcharge'])
        ->paginate();

        return [
            'surcharge_fees' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Surcharge Fees';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Import Surcharge Fees')
                ->modal('uploadSurchargeFeesModal')
                ->method('upload')
                ->icon('upload'),
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
            Layout::table('surcharge_fees', [
                TD::make('id', 'ID')
                    ->width('100'),

                TD::make('surcharge', _('Surcharge'))->render(function (SurchargeFee $surchargeFee) {
                    return $surchargeFee->surcharge->name;
                }),

                TD::make('course', _('Course'))->render(function (SurchargeFee $surchargeFee) {
                    return $surchargeFee->course->name;
                }),

                TD::make('fee', 'Course Fee')
                ->width('150')
                    ->usingComponent(Currency::class, before: 'Ush')
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('created_at', __('Created On'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),
            ]),

            Layout::modal('uploadSurchargeFeesModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Surcharge Fees'),
            ]))
                ->title('Upload Surcharge Fees')
                ->applyButton('Upload Surcharge Fees'),
        ];
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
            Excel::import(new SurchargeFeeImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Surcharge fee data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Surcharge fees data imported successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during import
            Alert::error($e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}
