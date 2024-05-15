<?php

namespace App\Orchid\Screens;

use App\Exports\PackingListExport;
use App\Models\Course;
use App\Models\Institution;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
use App\Models\StudentRegistration;
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

        $query = StudentRegistration::select(
            'institutions.code AS institution_code',
            'institutions.short_name AS institution_center',
            'institutions.institution_name',
            'courses.course_code AS course_code',
            'papers.abbrev'
        )
            ->join('registrations', 'registrations.id', '=', 'student_registrations.registration_id')
            ->join('registration_periods', 'registration_periods.id', '=', 'registrations.registration_period_id')
            ->join('papers', function ($join) {
                $join->on(DB::raw('JSON_CONTAINS(student_registrations.course_codes, CONCAT(\'"\', papers.code, \'"\'))'), '>', DB::raw('0'));
            })
            ->join('institutions', 'institutions.id', '=', 'registrations.institution_id')
            ->join('courses', 'courses.id', '=', 'registrations.course_id')
            ->where('student_registrations.course_codes', '!=', '')
            ->where('registration_periods.flag', '=', 1)
            ->limit(10);


        dd($query->get()->toJson());
        return [
            'report' =>
                $query->get()
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
