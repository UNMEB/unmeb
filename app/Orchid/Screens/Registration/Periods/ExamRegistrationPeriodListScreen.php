<?php

namespace App\Orchid\Screens\Registration\Periods;

use App\Models\ExamRegistration;
use App\Models\ExamRegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ExamRegistrationPeriodListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'examPeriods' => ExamRegistrationPeriod::latest()->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registration Periods';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Exam Period')
            ->modal('createRegistrationPeriodModal')
            ->method('create')
            ->icon('plus'),
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
            Layout::table('examPeriods', [
                TD::make('id', 'ID')
                    ->width('100'),
                TD::make('start_date', 'Start Date'),
                TD::make('end_date', 'End Date'),
                TD::make('academic_year', 'Academic Year'),

                TD::make('is_active', _('Status'))
                ->render(function ($examPeriod) {
                    if ($examPeriod->is_active === 1) {
                        return __('Active'); // You can replace 'Yes' with your custom label
                    } else {
                        return __('Inactive'); // You can replace 'No' with your custom label
                    }
                }),

                TD::make('created_at', __('Created On'))
                ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make(__('Actions'))
                ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (ExamRegistrationPeriod $data) {
                        $editButton = ModalToggle::make('Edit Period')
                        ->modal('editDataModal')
                        ->modalTitle('Edit Period ' . $data->academic_year)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'examPeriod' => $data->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                        ->confirm('Are you sure you want to delete this period?')
                        ->method('delete', [
                            'id' => $data->id
                        ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })
            ]),
            Layout::modal('createRegistrationPeriodModal', Layout::rows([
                Input::make('examPeriod.academic_year')
                ->title('Academic Year')
                ->placeholder('Enter academic year')
                ->help('The name of the examPeriod e.g 2012-2013')
                ->horizontal(),

                Input::make('examPeriod.start_date')
                ->title('Start Date')
                ->type('date')
                ->placeholder('Enter start date')
                ->horizontal(),

                Input::make('examPeriod.end_date')
                ->title('End Date')
                ->type('date')
                ->placeholder('Enter Period End date')
                ->horizontal(),
            ]))
                ->title('Add Period')
                ->applyButton('Add Period'),

            Layout::modal('editDataModal', Layout::rows([

                Input::make('examPeriod.academic_year')
                ->title('Academic Year')
                ->placeholder('Enter academic examPeriod')
                ->help('The name of the examPeriod e.g 2012-2013')
                ->horizontal(),

                Input::make('examPeriod.start_date')
                ->title('Start Date')
                ->type('date')
                ->placeholder('Enter examPeriod name')
                ->help('The name of the examPeriod e.g 2012')
                ->horizontal(),

                Input::make('examPeriod.end_date')
                ->title('End Date')
                ->type('date')
                ->placeholder('Enter examPeriod name')
                ->help('The name of the examPeriod e.g 2012')
                ->horizontal(),

                Select::make('examPeriod.is_active')
                ->options([
                    1  => 'Active',
                    0  => 'Inactive',
                ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive Exam period')
                    ->horizontal()
                    ->empty('No select')
            ]))->async('asyncGetExamRegistrationPeriod'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetExamRegistrationPeriod(ExamRegistrationPeriod $examPeriod): iterable
    {
        return [
            'examPeriod' => $examPeriod,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate([
            'examPeriod.start_date' => 'required',
            'examPeriod.end_date' => 'required',
            'examPeriod.academic_year' => 'required'
        ]);

        $examPeriod = new ExamRegistrationPeriod();
        $examPeriod->start_date = $request->input('examPeriod.start_date');
        $examPeriod->end_date = $request->input('examPeriod.end_date');
        $examPeriod->academic_year = $request->input('examPeriod.academic_year');
        $examPeriod->save();

        Alert::success("Year was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, ExamRegistrationPeriod $examPeriod): void
    {
        $request->validate([
            'examPeriod.academic_year',
            'examPeriod.start_date',
            'examPeriod.end_date',
            'examPeriod.is_active'
        ]);

        $examPeriod->fill($request->input('examPeriod'))->save();

        Alert::info(__('Year was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        ExamRegistrationPeriod::findOrFail($request->get('id'))->delete();

        Alert::success("Exam period was deleted.");
    }
}
