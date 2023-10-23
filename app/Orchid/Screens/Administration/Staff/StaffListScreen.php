<?php

namespace App\Orchid\Screens\Administration\Staff;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class StaffListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'staff' => Staff::with('institution')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Staff Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all registered staff, including institutions they belong to.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add Staff'))
                ->icon('bs.plus-circle')
                ->route('platform.administration.staff.create'),
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
            Layout::table('staff', [
                TD::make('id', 'ID'),
                TD::make('staff_name', 'Name'),
                TD::make('designation', 'Designation'),
                TD::make('status', 'Status'),
                TD::make('education', 'Education'),
                TD::make('qualification', 'Qualification'),
                TD::make('council', 'Council'),
                TD::make('reg_no', 'Registration'),
                TD::make('lic_exp', 'License'),
                TD::make('telephone', 'Telephone'),
                TD::make('email', 'Email'),
                TD::make('bank', 'Bank')->defaultHidden(),
                TD::make('branch', 'Branch')->defaultHidden(),
                TD::make('acc_no', 'Account')->defaultHidden(),
                TD::make('acc_name', 'Account Name')->defaultHidden(),
                TD::make('receipt', 'Receipt')->defaultHidden(),
                TD::make('institution.institution_name', 'Institution'),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Staff $staff) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([

                            Link::make(__('Edit'))
                                ->route('platform.administration.staff.edit', $staff->id)
                                ->icon('bs.pencil'),

                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                                ->method('remove', [
                                    'id' => $staff->id,
                                ]),
                        ])),
            ]),

            Layout::modal('asyncEditStaffModal', Layout::rows([
                Input::make('staff_name')->title('Name'),
                Input::make('designation')->title('Designation'),
                Input::make('status')->title('Status'),
                Input::make('education')->title('Education'),
                Input::make('qualification')->title('Qualification'),
                Input::make('council')->title('Council'),
                Input::make('reg_no')->title('Registration'),
                Input::make('lic_exp')->title('License'),
                Input::make('telephone')->title('Telephone'),
                Input::make('email')->title('Email'),
                Input::make('bank')->title('Bank'),
                Input::make('branch')->title('Branch'),
                Input::make('acc_no')->title('Account'),
                Input::make('acc_name')->title('Account Name'),
                Input::make('receipt')->title('Receipt'),
            ]))->async('asyncGetStaff'),
        ];
    }

    public function asyncGetStaff(Staff $staff): iterable
    {
        return [
            'staff' => $staff,
        ];
    }

    public function saveStaff(Request $request, Staff $staff): void
    {
        $request->validate([
            'staff.email' => [
                'required',
                Rule::unique(Staff::class, 'email')->ignore($staff),
            ],
        ]);

        $staff->fill($request->input('staff'))->save();

        Alert::info(__('Staff was saved.'));
    }

    public function remove(Request $request): void
    {
        Staff::findOrFail($request->get('id'))->delete();

        Alert::info(__('Staff was removed.'));
    }
}
