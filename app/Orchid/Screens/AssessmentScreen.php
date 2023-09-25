<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\CoursePaper;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\PracticalAssessmentMark;
use App\Models\Student;
use App\Models\TheoryAssessmentMark;
use App\Orchid\Layouts\PracticalAssessmentTable;
use App\Orchid\Layouts\TheoryAssessmentTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AssessmentScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        return [
            'theory_assessment' =>  [],
            'practical_assessment' => [],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Continuous Assessment';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make("select_institution")
                ->modal('selectInstitutionModal')
                ->method('change')
                ->modalTitle('Select Institution')

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
            Layout::modal('selectInstitutionModal', Layout::rows([
                Select::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'name')
                    ->empty('No Selection')
            ])),

            Layout::tabs([

                'Theory Assessment' => Layout::table('theory_assessment', [
                    TD::make('id', 'ID'),
                ]),

                'Practical Assessment' => Layout::table('practical_assessment', [
                    TD::make('id', 'ID'),
                ]),

                'Assessment Report' => Layout::table('practical_assessment', [
                    TD::make('id', 'ID'),
                ]),

            ])
        ];
    }


    public function change(Request $request)
    {
        // Modify the results returned based on this change
    }
}
