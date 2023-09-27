<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ExamPaymentScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::with(['course', 'institution', 'registrationPeriod'])
        ->whereHas('registrationPeriod', function ($query) {
            $query->where('flag', 1);
        });

        // dd($query->first()->toJson());
        return ['records' => $query->paginate()];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Exam Payments';
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
            Layout::table('records', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(fn (Registration $row) => optional($row->institution)->name),
                TD::make('course', 'Course')->render(fn ($row) => optional($row->course)->name),
                TD::make('amount', 'Amount')
                ->width('150')
                ->usingComponent(Currency::class, before: 'Ush')
                ->align(TD::ALIGN_RIGHT)
                    ->sort(),
                TD::make('receipt', 'Receipt')->render(function ($row) {
                    $imageFileName = $row->receipt;
                    $thumbnailUrl = url('books/' . $imageFileName); // Adjust the path as needed
                    $fullImageUrl = url('books/' . $imageFileName);

                    // Create an anchor tag with the thumbnail as the link and the full-size image as the target
                    return "<a href='$fullImageUrl' target='_blank'><img src='$thumbnailUrl' alt='Receipt' width='50' height='50' /></a>";
                }),
                TD::make('year_of_study', 'Year of Study'),
                TD::make('start_date', 'Registration Period Start Date')->render(fn ($row) => optional($row->registrationPeriod)->start_date),
                TD::make('end_date', 'Registration Period End Date')->render(fn ($row) => optional($row->registrationPeriod)->end_date),
                TD::make('actions', 'Actions')->render(function ($row) {
                    return  ModalToggle::make('Update')->modal('updatePaymentModdal')
                    ->modalTitle('Update Payment Details')
                    ->method('updatePayment')
                    ->asyncParameters([
                        'registration' => $row->id
                    ]);
                })
            ]),

            Layout::modal('updatePaymentModdal', Layout::rows([

                Input::make('registration.id')->hidden(),

                Input::make('registration.amount')
                ->title('Amount')

            ]))->async('asyncGetRecordDetails')
        ];
    }

    public function asyncGetRecordDetails(Registration $row)
    {
        return [
            'registration' => $row
        ];
    }

    public function updatePayment(Request $request, Registration $payment): void
    {
        $request->validate([
            'payment.amount' => 'required|numeric',
        ]);

        // Update the payment information
        $payment->update([
            'month' => $request->input('payment.month'),
            'amount' => $request->input('payment.amount'),
        ]);

        Alert::success('NSIN Payment Updated');
    }
}
