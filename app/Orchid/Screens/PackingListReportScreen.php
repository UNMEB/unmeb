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

            'report' => $results = StudentPaperRegistration::select(
                'i.code AS Institution',
                'i.short_name AS Center',
                'c.course_code AS Course',
                'p.abbrev AS Paper',
                'r.year_of_study AS Semester',
                'sr.trial AS Attempt',
                DB::raw('COUNT(sr.id) AS students')
            )
                ->join('course_papers AS cp', 'cp.id', '=', 'spr.course_paper_id')
                ->join('courses AS c', 'c.id', '=', 'cp.course_id')
                ->join('papers as p', 'p.id', '=', 'cp.paper_id')
                ->join('student_registrations AS sr', 'sr.id', '=', 'spr.student_registration_id')
                ->join('registrations AS r', 'r.id', '=', 'sr.registration_id')
                ->join('registration_periods AS rp', 'rp.id', '=', 'r.registration_period_id')
                ->join('institutions AS i', 'i.id', '=', 'r.institution_id')
                ->where('rp.flag', 1)
                ->groupBy('i.code', 'i.short_name', 'c.course_code', 'p.abbrev', 'r.year_of_study', 'sr.trial')
                ->orderBy('i.code')
                ->orderBy('i.short_name')
                ->orderBy('c.course_code')
                ->orderBy('p.abbrev')
                ->orderBy('r.year_of_study')
                ->orderBy('sr.trial')
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
