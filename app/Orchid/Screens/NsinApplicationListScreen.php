<?php

namespace App\Orchid\Screens;

use App\Models\Institution;
use App\Orchid\Layouts\ApplyForNSINsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class NsinApplicationListScreen extends Screen
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
        return 'NSIN Applications';
    }

    public function description(): ?string
    {
        return 'View NSIN Applications, application statuses. Filter NSIN Applications';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('New NSIN Applications')
                ->modal('newNSINApplicationModal')
                ->modalTitle('Create New NSIN Applications')
                ->class('btn btn-success')
                ->method('applyForNSINs')
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
            Layout::modal('newNSINApplicationModal', ApplyForNSINsForm::class)
                ->applyButton('Register for NSINs')
        ];
    }

    public function applyForNSINs(Request $request)
    {
        $institutionId = $request->get('institution_id');
        $nsin_registration_period_id = $request->get('nsin_registration_period_id');
        $courseId = $request->get('course_id');

        $url = route('platform.registration.nsin.applications.new', [
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'nsin_registration_period_id' => $nsin_registration_period_id
        ]);

        return redirect()->to($url);
    }
}
