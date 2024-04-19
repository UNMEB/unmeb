<?php

namespace App\Orchid\Screens\Registration\NSIN;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
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
    public $filters = [];

    public function __construct(Request $request)
    {
        session()->flush();
        $this->filters = $request->get("filter");
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $query = Student::query()
            ->select([
                'nr.id as registration_id',
                'i.institution_name',
                'i.id as institution_id', // Added institution_id
                'c.course_name',
                'c.id as course_id', // Added course_id
                'y.year as registration_year',
                'nr.month as registration_month',
                DB::raw('COUNT(*) as registrations_count'),
                DB::raw('MAX(nr.created_at) as latest_created_at'),
            ])
            ->from('students AS s')
            ->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->join('years as y', 'nr.year_id', '=', 'y.id')
            ->whereNull('nsr.nsin')
            ->where('nsr.verify', 0)
            ->groupBy('i.institution_name', 'i.id', 'c.course_name', 'c.id', 'registration_year', 'registration_month', 'registration_id');

        if (!empty($this->filters)) {
            if (isset($this->filters['institution_id']) && $this->filters['institution_id'] !== null) {
                $institutionId = $this->filters['institution_id'];
                $query->where('nr.institution_id', '=', $institutionId);
            }
            if (isset($this->filters['course_id']) && $this->filters['course_id'] !== null) {
                $courseId = $this->filters['course_id'];
                $query->where('c.id', '=', $courseId);
            }

            if (isset($this->filters['month']) && $this->filters['month'] !== null) {
                $month = $this->filters['month'];
                $query->where('nr.month', '=', $month);
            }

            if (isset($this->filters['year_id']) && $this->filters['year_id'] !== null) {
                $yearId = $this->filters['year_id'];
                $query->where('y.id', '=', $yearId);
            }
        }

        $registrations = $query->orderBy('registration_year', 'desc')
            ->orderBy('registration_month', 'desc')
            ->orderBy('registrations_count', 'desc')
            ->orderBy('latest_created_at', 'desc')
            ->paginate();


        return [
            'registrations' => $registrations
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Submitted NSIN Registrations';
    }

    public function description(): string|null
    {
        return 'View NSIN Application submission for all institutions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
                        ->fromModel(Institution::class, 'institution_name')
                        ->value(isset($this->filters['institution_id']) ? $this->filters['institution_id'] : null),


                    Relation::make('course_id')
                        ->title('Filter By Program')
                        ->fromModel(Course::class, 'course_name')
                        ->value(isset($this->filters['course_id']) ? $this->filters['course_id'] : null),

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
                        ->empty('None Selected')
                        ->value(isset($this->filters['month']) ? $this->filters['month'] : null),

                    Select::make('year_id')
                        ->fromModel(Year::class, 'year')
                        ->title('Filter By Year')
                        ->empty('None Selected')
                        ->value(isset($this->filters['year_id']) ? $this->filters['year_id'] : null)
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
                TD::make('registration_id', 'NR ID'),
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('registration_month', 'Month'),
                TD::make('registration_year', 'Year'),
                TD::make('registrations_count', 'Pending Approval')->render(fn($data) => "$data->registrations_count Students"),
                TD::make('actions', 'Actions')->render(
                    fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.nsin.approve.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'nsin_registration_id' => $data->registration_id
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

        return redirect()->to($url);
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

        // Sort student IDs based on surnames
        $students = Student::whereIn('id', $studentIds)->orderBy('surname')->pluck('id')->toArray();

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
        foreach ($students as $key => $studentId) {
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

                // Generate student code
                $studentCode = str_pad($key + 1, 3, '0', STR_PAD_LEFT);
                $nsinStudentRegistration->student_code = $studentCode;
                
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
