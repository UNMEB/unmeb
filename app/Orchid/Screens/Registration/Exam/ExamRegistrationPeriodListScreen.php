<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
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
        $query = RegistrationPeriod::query()
            ->orderBy('flag', 'desc');
        ;
        return [
            'periods' => $query->paginate()
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
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Add Period')
                ->modal('createPeriodModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Periods')
                ->modal('uploadPeriodsModal')
                ->method('upload')
                ->icon('upload'),
            Button::make('Export Data')
                ->method('download')
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
            Layout::table('periods', [
                TD::make('id', 'ID'),
                TD::make('reg_start_date', 'Start Date'),
                TD::make('reg_end_date', 'End Date'),
                TD::make('academic_year', 'Academic Year'),
                TD::make('flag', 'Is Active')->render(fn($data) => $data->flag == 1 ? 'Active' : 'Inactive'),

                TD::make(__('Actions'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (RegistrationPeriod $period) {
                        $editButton = ModalToggle::make('Edit Period')
                            ->modal('editPeriodModal')
                            ->modalTitle('Edit Period ' . $period->academic_year)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'period' => $period->id,
                            ])
                            ->class('btn btn-sm btn-success')
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this period?')
                            ->method('delete', [
                                'id' => $period->id
                            ])
                            ->class('btn btn-sm btn-danger')
                            ->render();

                        return Group::make([
                            $editButton,
                            $deleteButton
                        ])->fullWidth();
                    })
            ]),

            Layout::modal('createPeriodModal', Layout::rows([

                DateTimer::make('period.reg_start_date')
                    ->title('Registration Start Date')
                    ->horizontal(),

                DateTimer::make('period.reg_end_date')
                    ->title('Registration End Date')
                    ->horizontal(),

                // Academic Year
                Select::make('period.academic_year')
                    ->title('Academic Year')
                    ->options([
                        '2021-2022' => '2021-2022',
                        '2022-2023' => '2022-2023',
                        '2023-2024' => '2023-2024',
                        '2024-2025' => '2024-2025',
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                    ])
                    ->horizontal(),


                Select::make('period.flag')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive period flag')
                    ->horizontal()
                    ->empty('No select')
            ]))
                ->title('Create Period')
                ->applyButton('Create Period'),

            Layout::modal('editPeriodModal', Layout::rows([
                DateTimer::make('period.reg_start_date')
                    ->title('Registration Start Date')
                    ->horizontal(),

                DateTimer::make('period.reg_end_date')
                    ->title('Registration End Date')
                    ->horizontal(),

                // Academic Year
                Select::make('period.academic_year')
                    ->title('Academic Year')
                    ->options([
                        '2021-2022' => '2021-2022',
                        '2022-2023' => '2022-2023',
                        '2023-2024' => '2023-2024',
                        '2024-2025' => '2024-2025',
                        '2025-2026' => '2025-2026',
                        '2026-2027' => '2026-2027',
                        '2027-2028' => '2027-2028',
                        '2028-2029' => '2028-2029',
                        '2029-2030' => '2029-2030',
                    ])
                    ->horizontal(),


                Select::make('period.flag')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive period flag')
                    ->horizontal()
                    ->empty('No select')
            ]))->async('asyncGetPeriod'),
        ];
    }

    public function asyncGetPeriod(RegistrationPeriod $period): iterable
    {
        return [
            'period' => $period,
        ];
    }

    public function create(Request $request)
    {
        $request->validate([
            'period.reg_start_date' => 'required',
            'period.reg_end_date' => 'required',
            'period.academic_year' => 'required',
            'period.flag' => 'required'
        ]);

        $period = new RegistrationPeriod();
        $period->reg_start_date = $request->input('period.reg_start_date');
        $period->reg_end_date = $request->input('period.reg_end_date');
        $period->academic_year = $request->input('period.academic_year');
        $period->flag = $request->input('period.flag');
        $period->save();

        Alert::success("Exam registration period saved");
    }

    public function edit(Request $request, RegistrationPeriod $period): void
    {
        $request->validate([
            'period.reg_start_date' => 'required',
            'period.reg_end_date' => 'required',
            'period.academic_year' => 'required',
            'period.flag' => 'required'
        ]);

        $period->fill($request->input('period'))->save();

        Alert::info(__('Exam registration period updated.'));
    }
}
