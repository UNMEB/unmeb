<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use App\Models\Student;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NSINIncompleteDetailScreen extends Screen
{
    public $registration;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(NsinRegistration $registration): iterable
    {
        $registration->load(['studentsRegistrationNsin.student.district']);

        return [
            'students' => $registration->studentsRegistrationNsin->pluck('student')->unique(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Registered Students';
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
            Layout::table('students', [
                TD::make('id', 'ID'),
                TD::make('nsin', 'NSIN'),
                TD::make('name', 'Name')->render(function (Student $row) {
                    return $row->fullName;
                }),

                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district', 'Home District')->render(function (Student $row) {
                    return optional($row->district)->name;
                }),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email Address '),
                TD::make('actions', 'Actions')->render(function ($row) {
                    return Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Confirm to delete student information. Action cannot be reversed.'))
                        ->method('remove', [
                            'id' => $row->id,
                        ]);
                })
            ])
        ];
    }

    /**
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Student $data)
    {
        $data->delete();

        Alert::info(__('Student was deleted'));

        return redirect()->route('platform.index');
    }
}
