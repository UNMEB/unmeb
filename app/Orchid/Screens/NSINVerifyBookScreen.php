<?php

namespace App\Orchid\Screens;

use App\Models\BookPayment;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NSINVerifyBookScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $results = BookPayment::query()
            ->join('nsin_registrations', 'book_payments.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('registration_period_nsins', 'nsin_registrations.year_id', '=', 'registration_period_nsins.year_id')
            ->where('book_payments.approved', 0)
            ->where('registration_period_nsins.flag', 1)
            ->select('book_payments.*', 'institutions.name as institution_name', 'courses.name as course_name', 'number_of_students', 'total')
            ->orderBy('book_payments.created_at', 'desc')
            ->paginate();

        return [
            'payments' => $results
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Verify Log book And Research Guideline Payments';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('payments', [
                TD::make('id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Course'),
                TD::make('number_of_students', 'Number of Students'),
                TD::make('total', 'Total')
                ->width('150')
                ->usingComponent(Currency::class, before: 'Ush')
                ->align(TD::ALIGN_RIGHT)
                    ->sort(),
                TD::make('date_submitted', 'Date Submitted'),
                TD::make('receipt', 'Receipt')->render(function ($row) {
                    $imageFileName = $row->receipt;
                    $thumbnailUrl = url('books/' . $imageFileName); // Adjust the path as needed
                    $fullImageUrl = url('books/' . $imageFileName);

                    // Create an anchor tag with the thumbnail as the link and the full-size image as the target
                    return "<a href='$fullImageUrl' target='_blank'><img src='$thumbnailUrl' alt='Receipt' width='50' height='50' /></a>";
                }),
                TD::make('actions', 'Actions')->render(function ($row) {
                    return Button::make('Approve')->type(Color::DARK)
                        ->confirm('Confirm approval of receipt from ' . $row->institution_name . ' with a total of Ush ' . number_format($row->total))
                        ->method('approve', [
                            'id' => $row->id
                        ]);
                })
            ])
        ];
    }

    public function approve(Request $request)
    {
        $bookPayment = BookPayment::findOrFail($request->get('id'));

        if ($bookPayment) {
            // Update the 'approved' column to 1
            $bookPayment->update(['approved' => 1]);

            Alert::success('Book payment approved');
        } else {
            Alert::error('Payment record was not found');
        }
    }
}
