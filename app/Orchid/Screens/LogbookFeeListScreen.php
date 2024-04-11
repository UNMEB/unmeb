<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\LogbookFee;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;

class LogbookFeeListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Course::whereNotIn('id', function ($query) {
            $query->select('course_id')
                ->from('logbook_fees');
        });

        $query2 = LogbookFee::query();

        return [
            'courses' => $query->paginate(),
            'logbook_fees' => $query2->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Logbook Fees';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Create Logbook Fee')
                ->modal('newLogbookFee')
                ->modalTitle('Create Logbook Fee')
                ->method('submit')
                ->class('btn btn-success btn-sm')
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
                Relation::make('course_id')
                        ->title('Select Course')
                        ->fromModel(Course::class, 'course_name')
                        ->chunk(20),
            ]),

            Layout::table('logbook_fees', [
                TD::make('id', 'ID'),
                TD::make('course_id', 'Course Name')->render(fn(LogbookFee $logbookFee) => $logbookFee->course->course_name),
                TD::make('course_fee', 'Course Fee')->render(fn(LogbookFee $logbookFee) => 'Ush ' . number_format($logbookFee->course_fee)),
                TD::make('actions', 'Actions')->render(fn(LogbookFee $logbookFee) => DropDown::make()->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit Logbook Fee')
                            ->modalTitle('Edit Course Fee for ' . $logbookFee->course->course_name)
                            ->modal('editLogbookFeeModal')
                            ->method('edit')
                            ->asyncParameters([
                                'logbook_fee'=> $logbookFee->id,
                            ])
                    ])),
            ]),

            Layout::modal('newLogbookFee', Layout::rows([
                Relation::make('course_id')
                    ->fromModel(Course::class, 'course_name')
                    ->title('Course Name')
                    ->placeholder('Select Course')
                    ->required(),

                Input::make('course_fee')
                    ->required()
                    ->title('Enter course fee')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'Ush ',
                        'groupSeparator' => ',',
                        'digitsOptional' => true,
                    ])
                    ->help('Enter the exact amount to charge'),

            ])),

            Layout::modal('editLogbookFeeModal', Layout::rows([
                Relation::make('logbook_fee.course_id')
                    ->fromModel(Course::class, 'course_name')
                    ->title('Course Name')
                    ->placeholder('Select Course')
                    ->required(),

                Input::make('logbook_fee.course_fee')
                    ->required()
                    ->title('Enter course fee')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'Ush ',
                        'groupSeparator' => ',',
                        'digitsOptional' => true,
                    ])
                    ->help('Enter the exact amount to charge'),
            ]))
            ->applyButton('Update Fee')
            ->async('asyncGetLogbookFee')
        ];
    }

    /**
     * @return array
     */
    public function asyncGetLogbookFee(LogbookFee $logbookFee): iterable
    {
        return [
            'logbook_fee' => $logbookFee,
        ];
    }

    public function submit(Request $request)
    {
        $courseId = $request->input('course_id');
        $courseFee = Str::of($request->input('course_fee'))->replace(['Ush', ','], '')->trim()->toFloat();

        // Find the course by ID
        $course = Course::find($courseId);

        // Update the course fee
        if ($course) {
            $logbookFee = new LogbookFee();
            $logbookFee->course_id = $course->id;
            $logbookFee->course_fee = $courseFee;
            $logbookFee->save();

            Alert::success('Logbook Fee for Course has been saved');
        } else {
            Alert::error('Unable to find the requested Course');
        }
    }

    public function edit(Request $request, LogbookFee $fee):void
    {
        $fee->fill($request->input('course'))->save();

        Alert::success('Log Fee Updated');
    }
}
