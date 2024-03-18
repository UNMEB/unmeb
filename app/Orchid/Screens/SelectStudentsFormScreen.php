<?php

namespace App\Orchid\Screens;

use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use RealRashid\SweetAlert\Facades\Alert;

class SelectStudentsFormScreen extends Screen
{

    public $institutionId;
    public $numberOfStudents;
    public $action;

    public function __construct(Request $request)
    {
        $this->action = $request->get("action");
        if ($this->action == "PURCHASE_LOGBOOKS") {
            $this->institutionId = $request->get("institution_id");
            $this->numberOfStudents = $request->get("number_of_students");
        }
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        if ($this->action == "PURCHASE_LOGBOOKS") {
            // Alert::info('Purchase Logbooks', 'You want to purchase logbooks');

            $query = Student::query()->limit(50)->get();
        }

        return [
            'students' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if ($this->action == 'PURCHASE_LOGBOOKS') {
            return 'Select Students to purchase logbooks for';

        }
        return 'Select Students';
    }

    public function description(): string|null
    {
        return 'Select all students then scroll to the bottom of the page to submit';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('students', [
                TD::make('id', 'ID'),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                // TD::make('district.district_name', 'District'),
                // TD::make('country', 'Country'),
                // TD::make('location', 'Location'),
                TD::make('nin', 'NIN')->render(fn(Student $student) => $student->nin),
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
                TD::make('telephone', 'Phone Number'),
                TDCheckbox::make('students'),

            ]),

            Layout::view('submit_button')

        ];
    }

    public function submit(Request $request)
    {
        dd($request->all());
    }
}
