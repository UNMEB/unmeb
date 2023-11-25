<?php

namespace App\Orchid\Screens;

use App\Models\BiometricAccessLog;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentAccessLogListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $accessLog = BiometricAccessLog::filters();

        return [
            'access_log' => $accessLog->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Biometric Access';
    }

    public function description(): ?string
    {
        return 'View, filter and export student biometric logs';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Export Access Log')
                ->class('btn btn-sm btn-success link-success')
                ->action('export')
                ->rawClick(false),
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
                        ->title('Institution Name'),

                    // Filter By Student Name
                    Input::make('student_name')
                        ->title('Student Name'),
                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd()
            ])->title('Filter Records'),

            Layout::table('access_log', [
                TD::make('id', 'ID'),
                TD::make('institution.institution_name', 'Institution'),
                TD::make('course.course_name', 'Program'),
                TD::make('paper.paper_name', 'Paper'),
                TD::make('date','Date'),
            ])

        ];
    }
}
