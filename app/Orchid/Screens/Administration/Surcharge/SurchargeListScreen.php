<?php

namespace App\Orchid\Screens\Administration\Surcharge;

use App\Models\Surcharge;
use App\Models\SurchargeFee;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class SurchargeListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Surcharge::latest()
            ->get();

        return [
            'surcharges' => $query
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Surcharges';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Surcharge')
                ->modal('createSurchargeModal')
                ->method('create')
                ->icon('plus'),

            ModalToggle::make('Import Surcharge'),
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
            Layout::table('surcharges', [
                TD::make('id', 'ID')
                    ->width('100'),

                TD::make('name', 'Surcharge Name'),

                TD::make('flag', _('Surcharge Flag'))
                    ->render(function (Surcharge $surcharge) {
                        if ($surcharge->flag === 1) {
                            return __('Active'); // You can replace 'Yes' with your custom label
                        } else {
                            return __('Inactive'); // You can replace 'No' with your custom label
                        }
                    }),

                TD::make('actions', 'Actions')
                    ->width(180)
                    ->alignCenter()
                    ->render(function (Surcharge $surcharge) {
                        return  Group::make([
                            ModalToggle::make('Edit')
                                ->modal('editSurchargeModal')
                                ->modalTitle('Edit Surcharge ')
                                ->method('edit')
                                ->type(Color::LINK)
                                ->asyncParameters([
                                    'surcharge' => $surcharge->id,
                                ]),
                            Button::make('Delete')
                                ->confirm('Are you sure you want to delete this surcharge?')
                                ->method('delete', [
                                    'id' => $surcharge->id
                                ])
                                ->type(Color::LINK)
                        ]);
                    })
            ]),

            Layout::modal('createSurchargeModal', Layout::rows([
                Input::make('surcharge.name')
                    ->title('Surcharge Name')
                    ->placeholder('Enter name of surcharge')
                    ->horizontal(),
            ]))
                ->title('Create Surcharge')
                ->applyButton('Create Surcharge'),

            Layout::modal('editSurchargeModal', Layout::rows([
                Input::make('surcharge.name')
                    ->title('Surcharge Name')
                    ->placeholder('Enter name of surcharge')
                    ->horizontal(),

                Select::make('surcharge.flag')
                    ->options([
                        1  => 'Active',
                        0  => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive surcharge flag')
                    ->horizontal()
                    ->empty('No select')
            ]))
                ->title('Update Surcharge')
                ->applyButton('Update Surcharge')
                ->async('asyncGetSurcharge'),
        ];
    }

    /**
     * @return array
     */
    public function asyncGetSurcharge(Surcharge $surcharge): iterable
    {
        return [
            'surcharge' => $surcharge,
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
            'surcharge.name' => 'required'
        ]);

        $surcharge = new Surcharge();
        $surcharge->name = $request->input('surcharge.name');
        $surcharge->save();

        Alert::success("Year was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Surcharge $surcharge): void
    {
        $request->validate([
            'surcharge.name'
        ]);

        $surcharge->fill($request->input('surcharge'))->save();

        Alert::success(__('Year was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Surcharge::findOrFail($request->get('id'))->delete();

        Alert::success("Year was deleted.");
    }
}
