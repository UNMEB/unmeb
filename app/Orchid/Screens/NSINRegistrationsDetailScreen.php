<?php

namespace App\Orchid\Screens;

use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Orchid\Layouts\NSINRegistrationTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRegistrationsDetailScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $data = $request->all();

        // Check and handle null values for keys
        $nsin_registration_id = $data['nsin_registration_id'] ?? null;
        $institution_id = $request->get('institution_id') ?? null;
        $course_id = $request->get('course_id') ?? null;

        session()->put("nsin_registration_id", $nsin_registration_id);
        session()->put('institution_id', $institution_id);
        session()->put('course_id', $course_id);

        $query = Student::query()
            ->select([
                's.id',
                's.surname', 
                's.firstname', 
                's.othername', 
                's.dob', 
                's.gender',
                's.country_id', 
                's.district_id', 
                's.nin', 
                's.passport_number', 
                's.refugee_number',
                's.telephone',
                's.nsin'
                ])
            ->from('students as s')
            ->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->join('years as y', 'nr.year_id', '=', 'y.id')
            ->where('nsr.verify', 1)
            ->where('nr.institution_id', $institution_id)
            ->where('c.id', $course_id)
            ->where('nr.id', $nsin_registration_id);

        $registrations = $query->orderBy('surname', 'asc')
            ->paginate();

        return [
            'students' => $registrations
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            DropDown::make('Select Action')
            ->class('btn btn-primary btn-md')
            ->list([
                Button::make('Regenerate NSINs')
                ->icon('bs.receipt')
                ->class('btn link-success')
                ->method('regenerate'),

                Button::make('Delete NSINs')
                ->icon('bs.trash3')
                ->confirm(__('Once you confirm, all NSINs will be deleted for the current period'))
                ->method('delete')
                ->class('btn link-danger'),

            ])
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $table = (new NSINRegistrationTable);
        return [
            $table
        ];
    }

    public function regenerate(Request $request)
    {
    }

    public function delete(Request $request)
    {
    }
}
