<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Transaction;
use App\Models\Year;
use App\Orchid\Layouts\RegisterStudentsForNinForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ApproveNsinRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $query = Nsinregistration::filters()
            ->select([
                'r.id',
                'r.month',
                'c.course_name',
                'i.institution_name',
                'i.id as institution_id',
                'c.id as course_id',
                'y.year',
                DB::raw("(FLOOR(r.amount / 20000)) as students_to_register"),
                DB::raw("(SELECT COUNT(*) FROM nsin_student_registrations WHERE nsin_registration_id = r.id) as registered_students")
            ])
            ->from('nsin_registrations as r')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('years as y', 'r.year_id', '=', 'y.id')
            ->where('r.completed', 0)
            ->where('r.old', 0)
            ->orderBy('r.created_at', 'desc');

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
        return 'NSIN Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Register Student For NSIN')
                ->modal('registerStudentModal')
                ->method('register')
                ->icon('plus'),
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
            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Filter By Institution')
                        ->fromModel(Institution::class, 'institution_name'),

                    Relation::make('course_id')
                        ->title('Filter By Program')
                        ->fromModel(Course::class, 'course_name'),

                    Select::make('month')
                        ->title('Filter By Month')
                        ->options([
                            'January' => 'January',
                            'February' => 'February',
                            'March' => 'March',
                            'April' => 'April',
                            'May' => 'May',
                            'June' => 'June',
                            'July' => 'July',
                            'August' => 'August',
                            'September' => 'September',
                            'October' => 'October',
                            'November' => 'November',
                            'December' => 'December',
                        ])
                        ->empty('None Selected'),

                    Select::make('year_id')
                        ->fromModel(Year::class, 'year')
                        ->title('Filter By Year')
                        ->empty('None Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),
            Layout::table('registrations', [
                TD::make('id', 'ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('month', 'Month'),
                TD::make('year', 'Year'),
                TD::make('students_to_register', 'Students to Register'),
                TD::make('registered_students', 'Registered Students'),
                TD::make('actions', 'Actions')->render(
                    fn ($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.nsin.approve.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'nsin_registration_id' => $data->id
                        ])
                )
            ]),

            Layout::modal('registerStudentModal', RegisterStudentsForNinForm::class)
                ->title('Register Students For NSIN'),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $month = $request->input('month');
        $yearId = $request->input('year_id');

        $filters = [];

        if (!empty($institutionId)) {
            $filters['filter[institution_id]'] = $institutionId;
        }

        if (!empty($courseId)) {
            $filters['filter[course_id]'] = $courseId;
        }

        if (!empty($month)) {
            $filters['filter[month]'] = $month;
        }

        if (!empty($yearId)) {
            $filters['filter[year_id]'] = $yearId;
        }

        $url = route('platform.registration.nsin.approve', $filters);

        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.registration.nsin.approve');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function register(Request $request)
    {
        $nrpID = $request->get('nsin_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $studentIds = $request->get('student_ids');

        $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);

        $yearId = $nsinRegistrationPeriod->year_id;
        $month = $nsinRegistrationPeriod->month;

        $fee = config('settings.fess.nsin_registration') * count($studentIds);

        $institution = Institution::find($institutionId);

        if ($fee > $institution->account->balance) {
            Alert::error('Account balance too low to complete this transaction. Please top up to continue');
            return;
        }

        // Find the NSIN registration
        $nsinRegistration = NsinRegistration::where([
            'year_id' => $yearId,
            'month' => $month,
            'institution_id' => $institutionId,
            'course_id' => $courseId,
        ])->first();

        if ($nsinRegistration) {
            // Increment the amount
            $nsinRegistration->amount = $nsinRegistration->amount + $fee;

            // Save
            $nsinRegistration->save();
        } else {
            $nsinRegistration = new NsinRegistration();
            $nsinRegistration->year_id = $yearId;
            $nsinRegistration->month = $month;
            $nsinRegistration->institution_id = $institutionId;
            $nsinRegistration->course_id = $courseId;
            $nsinRegistration->amount = $fee;
            $nsinRegistration->save();
        }

        // For each student in the list create a NsinStudentRegistration if not already registered
        foreach ($studentIds as $studentId) {
            // Check if the student is already registered for the same period, institution, and course
            $existingRegistration = NsinStudentRegistration::where([
                'nsin_registration_id' => $nsinRegistration->id,
                'student_id' => $studentId,
                'verify' => 0
            ])->first();

            if (!$existingRegistration) {
                $nsinStudentRegistration = new NsinStudentRegistration();
                $nsinStudentRegistration->nsin_registration_id = $nsinRegistration->id;
                $nsinStudentRegistration->student_id = $studentId;
                $nsinStudentRegistration->verify = 0;
                $nsinStudentRegistration->save();
            }
        }

        // Create Transaction
        $newBalanace = $institution->account->balance - $fee;
        $institution->account->update([
            'balance' => $newBalanace,
        ]);

        $transaction = new Transaction([
            'amount' => $fee,
            'type' => 'debit',
            'account_id' => $institution->account->id,
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
            'status' => 'approved',
            'comment' => 'SYSTEM ' . now() . ':: NSIN Registration',
        ]);

        $transaction->save();

        Alert::success('Registration successful');
    }
}
