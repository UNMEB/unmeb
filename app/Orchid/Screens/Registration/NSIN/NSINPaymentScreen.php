<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\NsinRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NSINPaymentScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $payments = Nsinregistration::where('old', 1)
        ->with(['course', 'institution', 'year'])
        ->paginate();

        return [
            'payments' => $payments
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Registration NSIN Payments';
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
                TD::make('institution', 'Institution')->render(fn (NsinRegistration $data) => $data->institution->name),
                TD::make('course', 'Course')->render(fn (NsinRegistration $data) => $data->course->name),
                TD::make('amount', 'Amount')
                ->width('150')
                ->usingComponent(Currency::class, before: 'Ush')
                ->align(TD::ALIGN_RIGHT)
                    ->sort(),
                TD::make('receipt', 'Receipt'),
                TD::make('month', 'Month'),
                TD::make('year', 'Year')->render(fn (NsinRegistration $data) => $data->year->name),
                TD::make('actions', 'Actions')->render(function ($row) {
                    return ModalToggle::make('Update Payment')
                    ->modal('updatePaymentModal')
                    ->modalTitle('Update Payment')
                    ->method('updatePayment')
                    ->asyncParameters([
                        'payment' => $row->id
                    ]);
                })
            ]),

            Layout::modal('updatePaymentModal', Layout::rows(

                [
                    //
                    Input::make('payment.institution.id')
                    ->hidden(),

                    Input::make('payment.course.id')
                    ->hidden(),

                Input::make('payment.institution.name')
                ->title('Institution Name')
                        ->readonly(),

                Input::make('payment.course.name')
                ->title('Course Name')
                ->readonly(),

                Select::make('payment.month')
                ->title('Month')
                ->options([
                    'January' => 'January',
                    'May' => 'May',
                    'July' => 'July',
                    'November' => 'November',
                ])
                    ->empty('No Select'),

                Input::make('payment.amount')
                ->title('Amount'),
            ]))
                ->async('asyncGetPayment')
        ];
    }

    /**
     * @return array
     */
    public function asyncGetPayment(NsinRegistration $payment): iterable
    {
        return [
            'payment' => $payment,
        ];
    }

    public function updatePayment(Request $request, NsinRegistration $payment): void
    {
        $request->validate([
            'payment.month' => [
                'required',
                Rule::in(['January', 'May', 'July', 'November']),
            ],
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
