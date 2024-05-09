<?php

namespace App\Orchid\Screens;

use App\Exports\PackingListExport;
use App\Models\Course;
use App\Models\Institution;
use App\Models\RegistrationPeriod;
use App\Models\StudentPaperRegistration;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;


use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class PackingListReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        return [

            'report' => StudentPaperRegistration::query()
                ->from('student_paper_registration')
                ->join('course_paper', 'student_paper_registration.course_paper_id', '=', 'course_paper.id')
                ->join('student_registrations', 'student_paper_registration.student_registration_id', '=', 'student_registrations.id')
                ->join('courses', 'course_paper.course_id', '=', 'courses.id')
                ->join('papers', 'course_paper.paper_id', '=', 'papers.id')
                ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
                ->join('registration_periods', 'registrations.registration_period_id', '=', 'registration_periods.id')
                ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
                ->select(
                    'institutions.code AS Institution',
                    'institutions.short_name AS Center',
                    'registration_periods.id as registration_period_id',
                    'registrations.id as registration_id',
                    'registrations.year_of_study AS Year Of Study',
                    'courses.course_code AS Course',
                    'papers.abbrev AS Paper',
                    'student_registrations.trial as attempt',
                    DB::raw('COUNT(*) as registration_count'),
                    'registration_periods.reg_start_date',
                    'registration_periods.reg_end_date'
                )
                ->where('student_registrations.sr_flag', 1)
                ->where('registration_periods.flag', 1)
                ->groupBy(
                    'institutions.code',
                    'institutions.short_name',
                    'registration_periods.id',
                    'registrations.id',
                    'registrations.year_of_study',
                    'courses.course_code',
                    'papers.abbrev',
                    'student_registrations.trial'
                )
                ->get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Packing List';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Export Packing List')
                ->method('export')
                ->id('download')
                ->rawClick(true)
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
                    Relation::make('Registration Period')
                        ->title("Filter By Registration")
                        ->placeholder("Registration Period")
                        ->fromModel(RegistrationPeriod::class, 'id')
                        ->displayAppend('startAndEndDate'),

                    Relation::make('Institution')
                        ->title("Filter By Institution")
                        ->placeholder("Institution")
                        ->fromModel(Institution::class, 'institution_name')
                        ->canSee(!auth()->user()->inRole('institution')),

                    // Select Year of Study
                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
                            'Year 3 Semester 2' => 'Year 3 Semester 2',
                        ]),

                    Relation::make('Program')
                        ->title("Filter Programs")
                        ->placeholder("Program")
                        ->multiple()
                        ->fromModel(Course::class, 'course_name'),

                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),

            ])->title("Filter Packing List"),
            Layout::view('packing_list')
        ];
    }

    public function filter(Request $request)
    {
    }

    public function reset(Request $request)
    {
    }

    /**
     * Export as CSV
     */
    public function export(Request $request)
    {
    }
}
