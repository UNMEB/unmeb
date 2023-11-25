<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Registration;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AcceptedExamRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::filters()
            ->from('institutions as i')
            ->join('registrations as r', 'i.id', '=', 'r.institution_id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->select('i.id AS institution_id', 'i.institution_name', 'r.id as registration_id', 'c.id as course_id', 'c.course_name', 'rp.id as registration_period_id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->groupBy('i.id', 'i.institution_name', 'r.id', 'c.course_name', 'rp.id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->orderBy('r.updated_at', 'desc');

        return [
            'results' => $query->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Accepted Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Export Data')
                ->method('export')
                ->icon('download')
                ->class('btn btn-primary btn-sm link-primary')
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
                        ->title('Filter By Institution'),

                    Input::make('course_name')
                        ->title('Filter By Program'),
                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])
                ->title('Filter Results'),

            Layout::table('results', [
                TD::make('registration_id', 'ID'),
                TD::make('institution_name', 'Institution Name'),
                TD::make('course_name', 'Course Name'),
                TD::make('register_count', 'All Registrations')
                    ->render(function ($data) {
                        $regs = StudentRegistration::query()
                            ->where('registration_id', $data->registration_id)
                            ->count();
                        return $regs;
                    }),
                TD::make('register_count', 'Accepted Registrations')
                    ->render(function ($data) {
                        $regs = StudentRegistration::query()
                            ->where('registration_id', $data->registration_id)
                            ->where('sr_flag', 1)
                            ->count();
                        return $regs;
                    }),
                TD::make('actions', 'Actions')->render(function ($data) {
                    return Link::make('View Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.exam.accepted.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'registration_id' => $data->registration_id
                        ]);
                }),
            ])
        ];
    }

    public function filter(Request $request)
    {
        $institutionName = $request->input('institution_name');
        $courseName = $request->input('course_name');


        $filterParams = [];

        if (!empty($institutionName)) {
            $filterParams['filter[institution_name]'] = $institutionName;
        }

        if (!empty($courseName)) {
            $filterParams['filter[course_name]'] = $courseName;
        }

        $url = route('platform.registration.exam.accepted', $filterParams);

        return redirect()->to($url);

    }

    public function reset(Request $request)
    {
        $url = route('platform.registration.exam.accepted');
        
        return redirect()->to($url);
    }
}
