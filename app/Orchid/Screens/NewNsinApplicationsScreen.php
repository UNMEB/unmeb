<?php

namespace App\Orchid\Screens;

use App\Models\District;
use App\Models\Institution;
use App\Models\LogbookFee;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForNSINForm;
use App\Orchid\Layouts\RegisterStudentsForNSINTable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NewNsinApplicationsScreen extends Screen
{
    public $institutionId;
    public $courseId;
    public $nsinRegistrationPeriodId;

    public function __construct(Request $request)
    {
        session()->forget(['institution_id', 'course_id', 'nsin_registration_id']);

        $this->institutionId = request()->get('institution_id');
        $this->courseId = request()->get('course_id');
        $this->nsinRegistrationPeriodId = request()->get('nsin_registration_period_id');

        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $nsinRegistrationPeriodId = $request->input('nsin_registration_period_id');

        // Save to session
        session()->put('institution_id', $institutionId);
        session()->put('course_id', $courseId);
        session()->put('nsin_registration_period_id', $nsinRegistrationPeriodId);
    }


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $currentPeriod = NsinRegistrationPeriod::query()->where('id', session('nsin_registration_period_id'))->first();

        $query = Student::withoutGlobalScopes()
        ->from('students as s')
        ->whereNull('s.nsin')
        ->orderBy('s.surname', 'asc');

        if(auth()->user()->inRole('institution')) {
            $query->where('s.institution_id', auth()->user()->institution_id);
        }

        return [
            'students' => $query->paginate(100)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Apply Students For NSINs';
    }

    public function description(): ?string
    {
        return 'Select students for NSIN Application from the table below and submit';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
            Layout::rows([

                Group::make([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->chunk(20),

                    Input::make('name')
                        ->title('Filter By Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('Filter By District of origin'),

                    Select::make('gender')
                        ->title('Filter By Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title("Filter Students"),
            RegisterStudentsForNSINTable::class
        ];
    }

    public function submit(Request $request)
    {
        $nrpID = session('nsin_registration_period_id');
        $institutionId = session('institution_id');
        $courseId = session('course_id');

        $settings = \Config::get('settings');
        $nsinRegistrationFee = $settings['fees.nsin_registration'];
        $logbookFee = LogbookFee::firstWhere('course_id', $courseId);

        if($logbookFee == null) {
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed','Logbook Fee for this course is not yet set. Please contact support at UNMEB');
            return back();
        }

        // Log the start of the function
        \Log::info('NSIN registration submission started.');

        $students = collect($request->get('students'))->keys();

        if ($students->count() == 0) {
            Alert::error('Unable to submit data. You have not selected any students to register');
            return;
        }

       
        $studentIds = collect($request->get('students'))->values();

        // Log session and input data
        \Log::info('Session data:', [
            'nsin_registration_period_id' => $nrpID,
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'student_ids' => $studentIds,
            'logbook_fee' => $logbookFee->course_fee
        ]);

        $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);

        $yearId = $nsinRegistrationPeriod->year_id;
        $month = $nsinRegistrationPeriod->month;

        $institution = Institution::find($institutionId);

        // Log institution data
        \Log::info('Institution data:', ['institution' => $institution]);

        foreach ($studentIds as $studentId) {
            $fees = $nsinRegistrationFee + $logbookFee->course_fee;

            if ($fees > $institution->account->balance) {
                Alert::error('Account balance too low to complete this transaction. Please top up to continue');
                return;
            }

            // Log balance check
            \Log::info('Account balance check passed.');

            // Find or create the NSIN registration
            $nsinRegistration = NsinRegistration::firstOrCreate([
                'year_id' => $yearId,
                'month' => $month,
                'institution_id' => $institutionId,
                'course_id' => $courseId,
            ]);

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

                // Create Transaction for NSIN registration fee for this student
                $nsinTransaction = new Transaction([
                    'amount' => $nsinRegistrationFee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'NSIN Registration Fee for Student ID: ' . $studentId,
                ]);

                $nsinTransaction->save();

                // Create Transaction for logbook fee for this student
                $logbookTransaction = new Transaction([
                    'amount' => $logbookFee->course_fee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Logbook Fee for Student ID: ' . $studentId,
                ]);

                $logbookTransaction->save();

                // Update institution account balance
                $newBalance = $institution->account->balance - $fees;

                // Log new balance calculation
                \Log::info('New balance calculated:', ['new_balance' => $newBalance]);

                $institution->account->update([
                    'balance' => $newBalance,
                ]);
            }
        }

        Alert::success('Registration successful');

        // Log success message
        \Log::info('NSIN registration successful.');

        // Redirect
        return redirect()->back();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {

        $institutionId = $request->input('institution_id');
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty($name)) {
            $filterParams['filter[name]'] = $name;
        }

        if (!empty($gender)) {
            $filterParams['filter[gender]'] = $gender;
        }

        if (!empty($district)) {
            $filterParams['filter[district_id]'] = $district;
        }

        $url = route('platform.registration.nsin.applications.new', $filterParams);

        return redirect()->to($url);
    }



}
