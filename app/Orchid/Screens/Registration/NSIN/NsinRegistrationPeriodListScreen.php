<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistrationPeriod;
use App\Models\Year;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation as FieldsRelation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NsinRegistrationPeriodListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistrationPeriod::with('year')->orderBy('flag', 'desc');
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
        return 'NSIN Registration Periods';
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
                TD::make('month', 'Month'),
                TD::make('year.year', 'Year'),
                TD::make('flag', 'Is Active'),
                TD::make('flag', 'Is Active')->render(fn($data) => $data->flag == 1 ? 'Active' : 'Inactive'),
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
                    ->render(function (NsinRegistrationPeriod $period) {
                        $editButton = ModalToggle::make('Edit Period')
                            ->modal('editPeriodModal')
                            ->modalTitle('Edit Period ' . $period->year)
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

                FieldsRelation::make('period.year_id')
                    ->title('Period Year')
                    ->fromModel(Year::class, 'year', 'id')
                    ->horizontal(),

                Select::make('period.month')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ])
                    ->title('Period Month')
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

                FieldsRelation::make('period.year_id')
                    ->title('Period Year')
                    ->fromModel(Year::class, 'year', 'id')
                    ->horizontal(),

                Select::make('period.month')
                    ->title('Period Month')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
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

    /**
     * @return array
     */
    public function asyncGetPeriod(NsinRegistrationPeriod $period): iterable
    {
        return [
            'period' => $period,
        ];
    }

    public function create(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'period.year_id' => 'required',
            'period.month' => 'required',
            'period.flag' => 'required'
        ]);

        // Create a new NsinRegistrationPeriod record with the validated data
        $period = new NsinRegistrationPeriod();
        $period->year_id = $request->input('period.year_id');
        $period->month = $request->input('period.month');
        $period->flag = $request->input('period.flag');
        $period->save();

        // Redirect back with success message
        return redirect()->back()->with('success', 'NSIN registration period has been created successfully.');
    }
}
