<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\ExamRegistration;
use App\Orchid\Layouts\ApproveExamRegistrationListener;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ApproveExamRegistrationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = ExamRegistration::with('institution', 'course', 'student', 'examRegistrationPeriod');
        $query->where([
            'is_completed' => 1,
            'is_approved' => 0
        ]);

        return [
            'registrations' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Approve/Decline Exam Registration';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('approve')
            ->method('approve'),
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
            ApproveExamRegistrationListener::class
        ];
    }

    public function approve(Request $request)
    {
        dd($request->all());
    }
}
