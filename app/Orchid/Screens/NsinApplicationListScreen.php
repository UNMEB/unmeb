<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ApplyForNSINsForm;
use DB;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NsinApplicationListScreen extends Screen
{
    public $filters = [];

    public function __construct(Request $request)
    {
        session()->flush();
        $this->filters = $request->get("filter");
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Student::withoutGlobalScopes()
            ->select([
                'nr.id as registration_id',
                'i.institution_name',
                'i.id as institution_id', // Added institution_id
                'c.course_name',
                'c.id as course_id', // Added course_id
                'y.year as registration_year',
                'nr.month as registration_month',
                DB::raw('COUNT(*) as registrations_count'),
                DB::raw('MAX(nr.created_at) as latest_created_at'),
            ])
            ->from('students AS s')
            ->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->join('years as y', 'nr.year_id', '=', 'y.id')
            ->whereNull('nsr.nsin')
            ->groupBy('i.institution_name', 'i.id', 'c.course_name', 'c.id', 'registration_year', 'registration_month', 'registration_id');


        if(auth()->user()->inRole('institution')) {
            $query->where('nr.institution_id', auth()->user()->institution_id);
        }
   
        $query->orderBy('registration_year', 'desc');

        return [
            'applications' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'NSIN Applications';
    }

    public function description(): ?string
    {
        return 'View NSIN Applications, application statuses. Filter NSIN Applications';
    }


    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('New NSIN Applications')
                ->modal('newNSINApplicationModal')
                ->modalTitle('Create New NSIN Applications')
                ->class('btn btn-success')
                ->method('applyForNSINs'),

            ModalToggle::make('Export NSIN Applications')
            ->class('btn btn-primary')
            ->modal('exportNSINApplications')
            ->modalTitle('Export NSIN Applications')
            ->method('exportNSINApplications')
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
            Layout::table('applications', [
                TD::make('registration_id', 'NR ID'),
                TD::make('institution_name', 'Institution')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Program'),
                TD::make('registration_month', 'Month'),
                TD::make('registration_year', 'Year'),
                TD::make('registrations_count', 'Pending Approval')->render(fn($data) => "$data->registrations_count Students"),
                TD::make('actions', 'Actions')->render(
                    fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.nsin.applications.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'nsin_registration_id' => $data->registration_id
                        ])
                )
            ])
        ];
    }


}
