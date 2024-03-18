<?php

namespace App\Orchid\Screens;

use App\Models\Institution;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class LogbookPurchaseScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Logbooks and Research Guidelines';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Purchase Now')
                ->class('btn btn-success')
                ->modalTitle('Purchase Logbooks & Research Guidelines')
                ->modal('logbookPurchaseModal')
                ->method('purchase')
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
            Layout::modal('logbookPurchaseModal', [
                Layout::rows([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->placeholder('Select User Institution')
                        ->fromModel(Institution::class, 'institution_name', 'id')
                        ->applyScope('userInstitutions')
                        ->value(auth()->user()->institution_id)
                        // ->disabled(!auth()->user()->hasAccess('platform.internals.all_institutions'))
                        ->required(),

                    Input::make('number_of_students')
                        ->title('Number of students')
                        ->placeholder('Select number of students')
                        ->min(1)
                        ->type('number')
                ])
            ])->applyButton('Select Students'),

        ];
    }

    public function purchase(Request $request)
    {
        $numberOfStudents = $request->get('number_of_students');
        $institutionId = $request->get('institution_id');

        $url = route('platform.actions.select_students_form', [
            'institution_id' => $institutionId,
            'number_of_students' => $numberOfStudents,
            'action' => 'PURCHASE_LOGBOOKS'
        ]);

        return redirect()->to($url);
    }
}
