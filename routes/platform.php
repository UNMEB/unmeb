<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\Student;
use App\Models\Ticket;
use App\Orchid\Screens\AccountTransactionListScreen;
use App\Orchid\Screens\ActivityLogListScreen;
use App\Orchid\Screens\Administration\Course\CourseAssignPapersListScreen;
use App\Orchid\Screens\Administration\Course\CourseListScreen;
use App\Orchid\Screens\Administration\District\DistrictListScreen;
use App\Orchid\Screens\Administration\Institution\InstitutionAssignCoursesListScreen;
use App\Orchid\Screens\Administration\Institution\InstitutionListScreen;
use App\Orchid\Screens\Administration\Paper\PaperListScreen;
use App\Orchid\Screens\Administration\Staff\StaffEditScreen;
use App\Orchid\Screens\Administration\Staff\StaffListScreen;
use App\Orchid\Screens\Administration\Student\StudentEditScreen;
use App\Orchid\Screens\Administration\Student\StudentListScreen;
use App\Orchid\Screens\Administration\Surcharge\SurchargeFeeListScreen;
use App\Orchid\Screens\Administration\Surcharge\SurchargeListScreen;
use App\Orchid\Screens\Administration\Years\YearListScreen;
use App\Orchid\Screens\Assessment\ContinuousAssessmentListScreen;
use App\Orchid\Screens\Assessment\InstitutionAssessmentListScreen;

use App\Orchid\Screens\Biometric\StudentEnrollmentListScreen;
use App\Orchid\Screens\Biometric\StudentVerificationListScreen;
use App\Orchid\Screens\Comment\CommentListScreen;
use App\Orchid\Screens\CourseUnAssignPapersListScreen;
use App\Orchid\Screens\ExamApplicationDetailScreen;
use App\Orchid\Screens\ExamApplicationListScreen;
use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\ExamRegistrationDetailScreen;
use App\Orchid\Screens\ExamRegistrationListScreen;
use App\Orchid\Screens\Finance\Account\AccountListScreen;
use App\Orchid\Screens\Finance\Account\InstitutionTransactionListScreen;
use App\Orchid\Screens\Finance\Account\PendingTransactionListScreen;
use App\Orchid\Screens\Finance\Account\TransactionListScreen;
use App\Orchid\Screens\InstitutionUnAssignCoursesListScreen;
use App\Orchid\Screens\LogbookPurchaseScreen;
use App\Orchid\Screens\NewExamApplicationScreen;
use App\Orchid\Screens\NewSupportRequestScreen;
use App\Orchid\Screens\NSINApplicationListDetails;
use App\Orchid\Screens\NSINRegistrationsDetailScreen;
use App\Orchid\Screens\NSINRegistrationsListScreen;
use App\Orchid\Screens\PackingListReportScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Registration\Exam\AcceptedExamRegistration;
use App\Orchid\Screens\Registration\Exam\AcceptedExamRegistrationDetails;
use App\Orchid\Screens\Registration\Exam\ApproveExamRegistration;
use App\Orchid\Screens\Registration\Exam\ApproveExamRegistrationDetails;
use App\Orchid\Screens\Registration\Exam\ExamRegistrationPeriodListScreen;
use App\Orchid\Screens\Registration\Exam\ExamRejectionReasons;
use App\Orchid\Screens\Registration\Exam\IncompleteExamRegistration;
use App\Orchid\Screens\Registration\Exam\IncompleteExamRegistrationDetails;
use App\Orchid\Screens\Registration\Exam\RejectedExamRegistration;
use App\Orchid\Screens\Registration\Exam\RejectedExamRegistrationDetails;
use App\Orchid\Screens\Registration\NSIN\AcceptedNsinRegistration;
use App\Orchid\Screens\Registration\NSIN\AcceptedNsinRegistrationDetails;
use App\Orchid\Screens\Registration\NSIN\ApproveNsinRegistration;
use App\Orchid\Screens\Registration\NSIN\ApproveNsinRegistrationDetails;
use App\Orchid\Screens\Registration\NSIN\IncompleteNsinRegistration;
use App\Orchid\Screens\Registration\NSIN\IncompleteNsinRegistrationDetails;
use App\Orchid\Screens\Registration\NSIN\NsinRegistrationPeriodListScreen;
use App\Orchid\Screens\Registration\NSIN\NsinRejectionReasons;
use App\Orchid\Screens\Registration\NSIN\RejectedNsinRegistration;
use App\Orchid\Screens\Registration\NSIN\RejectedNsinRegistrationDetails;
use App\Orchid\Screens\Reports\ExamRegistrationReportScreen;
use App\Orchid\Screens\Reports\NSINRegistrationReportScreen;
use App\Orchid\Screens\Reports\RegistrationReportScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\RollbackTransactionDetailScreen;
use App\Orchid\Screens\RollbackTransactionListScreen;
use App\Orchid\Screens\SelectStudentsFormScreen;
use App\Orchid\Screens\TicketListScreen;
use App\Orchid\Screens\TicketManagementScreen;
use App\Orchid\Screens\TicketResponseScreen;
use App\Orchid\Screens\UNMEBInformationScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\StudentAccessLogListScreen;
use App\Orchid\Screens\AddStudentAssessmentFormScreen;
use App\Orchid\Screens\StudentResearchListScreen;
use App\Orchid\Screens\AppSettingListScreen;
use App\Orchid\Screens\LogbookFeeListScreen;
use App\Orchid\Screens\NewNsinApplicationsScreen;
use App\Orchid\Screens\NsinApplicationListScreen;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Activity Log
Route::screen('/activity', ActivityLogListScreen::class)->name('platform.activity');

// Ticket Manager Screen
Route::screen('/tickets/manager', TicketManagementScreen::class)
    ->name('platform.tickets.manager');

// New Support Request
// Route::screen('/tickets/new', NewSupportRequestScreen::class)
//     ->name('platform.tickets.new');


// Platform > Tickets > New
Route::screen('tickets/new', TicketResponseScreen::class)
    ->name('platform.tickets.new')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.tickets')
        ->push(__('Create'), route('platform.tickets.new')));

// Ticket Response Screen
Route::screen('tickets/{ticket}/response', TicketResponseScreen::class)
    ->name('platform.tickets.response')
    ->breadcrumbs(fn(Trail $trail, Ticket $ticket) => $trail
        ->parent('platform.tickets')
        ->push($ticket->id, route('platform.tickets.response', $ticket)));

// Support Ticket
Route::screen('/tickets', TicketListScreen::class)
    ->name('platform.tickets')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Tickets'), route('platform.tickets')));

// UNMEB Updates Screen
Route::screen('updates', UNMEBInformationScreen::class)->name('platform.updates');

// Select Students Form Screen
Route::screen('select_students', SelectStudentsFormScreen::class)->name('platform.actions.select_students_form');

// Logbook & Research Guidelines Screen
Route::screen('logbooks', LogbookPurchaseScreen::class)->name('platform.system.finance.logbooks');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn(Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn(Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));



// Platform > Administration > Insitutions > Assign Programs
Route::screen('administration/institutions/{institution}/assign', InstitutionAssignCoursesListScreen::class)
    ->name('platform.institutions.assign')
    ->breadcrumbs(fn(Trail $trail, $institution) => $trail
        ->parent('platform.institutions')
        ->push($institution->institution_name, route('platform.institutions.assign', $institution)));

// Platform > Administration > Insitutions > Assign Programs
Route::screen('administration/institutions/{institution}/unassign', InstitutionUnAssignCoursesListScreen::class)
    ->name('platform.institutions.unassign')
    ->breadcrumbs(fn(Trail $trail, $institution) => $trail
        ->parent('platform.institutions')
        ->push($institution->institution_name, route('platform.institutions.unassign', $institution)));

// Platform > Administration > Institutions
Route::screen('administration/institutions', InstitutionListScreen::class)
    ->name('platform.institutions')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Institutions'),
                route('platform.institutions')
            )
    );

// Platform > Administration > Courses > Assign Papers
Route::screen('administration/courses/{course}/assign', CourseAssignPapersListScreen::class)
    ->name('platform.courses.assign')
    ->breadcrumbs(fn(Trail $trail, $course) => $trail
        ->parent('platform.courses')
        ->push($course->course_name, route('platform.courses.assign', $course)));


// Platform > Administration > Courses > Assign Papers
Route::screen('administration/courses/{course}/unassign', CourseUnAssignPapersListScreen::class)
    ->name('platform.courses.unassign')
    ->breadcrumbs(fn(Trail $trail, $course) => $trail
        ->parent('platform.courses')
        ->push($course->course_name, route('platform.courses.unassign', $course)));


// Platform > Administration > Programs
Route::screen('administration/courses', CourseListScreen::class)
    ->name('platform.courses')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Programs'),
                route('platform.courses')
            )
    );

// Platform > Administration > Papers
Route::screen('administration/papers', PaperListScreen::class)
    ->name('platform.papers')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Papers'),
                route('platform.papers')
            )
    );

// Platform > Administration > Years
Route::screen('administration/years', YearListScreen::class)
    ->name('platform.years')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Years'),
                route('platform.years')
            )
    );

// Platform > Administration > Districts
Route::screen('administration/districts', DistrictListScreen::class)
    ->name('platform.districts')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Districts'),
                route('platform.districts')
            )
    );

// Platform > Administration > Logbook Fees
Route::screen('administration/logbook_fees', LogbookFeeListScreen::class)
    ->name('platform.logbook_fees');

// Platform > Administration > Surcharges
Route::screen('administration/surcharges', SurchargeListScreen::class)
    ->name('platform.surcharges')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.index')
            ->push(
                __('Surcharges'),
                route('platform.surcharges')
            )
    );

// // Platform > Administration > Surcharge Fees
// Route::screen('administration/surcharge-fees', SurchargeFeeListScreen::class)
//     ->name('platform.surcharge-fees')
//     ->breadcrumbs(
//         fn(Trail $trail) => $trail
//             ->parent('platform.index')
//             ->push(
//                 __('Surcharge Fees'),
//             )
//     );

// Platform > Administration > Surcharge > Fees
Route::screen('administration/{surcharge}/fees', SurchargeFeeListScreen::class)
    ->name('platform.surcharge.fees')
    ->breadcrumbs(fn(Trail $trail, $surcharge) => $trail
        ->parent('platform.surcharges')
        ->push($surcharge->surcharge_name, route('platform.surcharge.fees', $surcharge)));

// Platform > Comments
Route::screen('comments', CommentListScreen::class)
    ->name('platform.comments.list');


// Platform > Administration > Staff > Edit
Route::screen('staff/{staff}/edit', StaffEditScreen::class)
    ->name('platform.staff.edit')
    ->breadcrumbs(fn(Trail $trail, $staff) => $trail
        ->parent('platform.staff')
        ->push($staff->staff_name, route('platform.staff.edit', $staff)));

// Platform > Administration > Staff > Create
Route::screen('staff/create', StaffEditScreen::class)
    ->name('platform.staff.create')
    ->breadcrumbs(
        fn(Trail $trail) => $trail
            ->parent('platform.staff')
            ->push(
                __('Create'),
                route('platform.staff.create')
            )
    );

// Platform > Administration > Staff
Route::screen('staff', StaffListScreen::class)
    ->name('platform.staff')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Staff'), route('platform.staff')));


// Platform > Administration > Students
Route::screen('students', StudentListScreen::class)
    ->name('platform.students')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Students'), route('platform.students')));

// NSIN Applications
Route::screen('registrations/nsin/registrations/list', NSINRegistrationsListScreen::class)
    ->name('platform.registration.nsin.registrations.list');

Route::screen('registrations/nsin/registrations/details', NSINRegistrationsDetailScreen::class)
    ->name('platform.registration.nsin.registrations.details');

Route::screen('registrations/nsin/applications/list', NsinApplicationListScreen::class)
    ->name('platform.registration.nsin.applications.list');

Route::screen('registrations/nsin/applications/new', NewNsinApplicationsScreen::class)
    ->name('platform.registration.nsin.applications.new');

Route::screen('registrations/nsin/applications/details', NSINApplicationListDetails::class)
    ->name('platform.registration.nsin.applications.details');


// Exam Applications
Route::screen('registrations/exam/applications/list', ExamApplicationListScreen::class)
    ->name('platform.registration.exam.applications.list');

Route::screen('registrations/exam/applications/new', NewExamApplicationScreen::class)
    ->name('platform.registration.exam.applications.new');

Route::screen('registrations/exam/applications/details', ExamApplicationDetailScreen::class)
    ->name('platform.registration.exam.applications.details');

// Exam Applications
Route::screen('registrations/exam/registrations/list', ExamRegistrationListScreen::class)
    ->name('platform.registration.exam.registrations.list');

// Exam Applications
Route::screen('registrations/exam/registrations/details', ExamRegistrationDetailScreen::class)
    ->name('platform.registration.exam.registrations.details');

// Platform > Registration > NSIN > Incomplete > Details
Route::screen('registrations/nsin/incomplete/details', IncompleteNsinRegistrationDetails::class)
    ->name('platform.registration.nsin.incomplete.details');

// Platform > Registration > NSIN > Incomplete
Route::screen('registration/nsin/incomplete', IncompleteNsinRegistration::class)
    ->name('platform.registration.nsin.incomplete')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Incomplete NSIN Registrations'), route('platform.registration.nsin.incomplete')));


// Platform > Registration > NSIN > Accepted > Details
Route::screen('registrations/nsin/accepted/details', AcceptedNsinRegistrationDetails::class)
    ->name('platform.registration.nsin.accepted.details');

// Platform > Registration > NSIN > Accepted
Route::screen('registration/nsin/accepted', AcceptedNsinRegistration::class)
    ->name('platform.registration.nsin.accepted')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Accepted NSIN Registrations'), route('platform.registration.nsin.accepted')));

// Platform > Registration > NSIN > Rejected > Details
Route::screen('registrations/nsin/rejected/details', RejectedNsinRegistrationDetails::class)
    ->name('platform.registration.nsin.rejected.details');

// Platform > Registration > NSIN > Rejected
Route::screen('registration/nsin/rejected', RejectedNsinRegistration::class)
    ->name('platform.registration.nsin.rejected')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Rejected NSIN Registrations'), route('platform.registration.nsin.rejected')));


// Platform > Registration > NSIN > Approve > Details
Route::screen('registrations/nsin/approve/details', ApproveNsinRegistrationDetails::class)
    ->name('platform.registration.nsin.approve.details');

// Platform > Registration > NSIN > Approve
Route::screen('registration/nsin/approve', ApproveNsinRegistration::class)
    ->name('platform.registration.nsin.approve')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Approve NSIN Registrations'), route('platform.registration.nsin.approve')));

// Platform > Registration > NSIN > NSIN Rejection Reasons
Route::screen('registration/nsin/rejection-reasons', NsinRejectionReasons::class)
    ->name('platform.registration.nsin.rejection-reasons')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('NSIN Rejection Reasons'), route('platform.registration.nsin.rejection-reasons')));


// Platform > Registration > Exam > Incomplete > Details
Route::screen('registrations/exam/incomplete/details', IncompleteExamRegistrationDetails::class)
    ->name('platform.registration.exam.incomplete.details');

// Platform > Registration > Exam > Incomplete
Route::screen('registration/exam/incomplete', IncompleteExamRegistration::class)
    ->name('platform.registration.exam.incomplete')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Exam Registrations'), route('platform.registration.exam.incomplete')));


// Platform > Registration > Exam > Accepted > Details
Route::screen('registrations/exam/accepted/details', AcceptedExamRegistrationDetails::class)
    ->name('platform.registration.exam.accepted.details');


// Platform > Registration > Exam > Accepted
Route::screen('registration/exam/accepted', AcceptedExamRegistration::class)
    ->name('platform.registration.exam.accepted')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Accepted Exam Registrations'), route('platform.registration.exam.accepted')));

// Platform > Registration > Exam > Rejected > Details
Route::screen('registrations/exam/rejected/details', RejectedExamRegistrationDetails::class)
    ->name('platform.registration.exam.rejected.details');


// Platform > Registration > Exam > Rejected
Route::screen('registration/exam/rejected', RejectedExamRegistration::class)
    ->name('platform.registration.exam.rejected')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Rejected Exam Registrations'), route('platform.registration.exam.rejected')));

// Platform > Registration > Exam > Approve > Details
Route::screen('registrations/exam/approve/details', ApproveExamRegistrationDetails::class)
    ->name('platform.registration.exam.approve.details');

// Platform > Registration > Exam > Approve
Route::screen('registration/exam/approve', ApproveExamRegistration::class)
    ->name('platform.registration.exam.approve')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Approve Exam Registrations'), route('platform.registration.exam.approve')));

// Platform > Registration > Exam > Exam Rejection Reasons
Route::screen('registration/exam/rejection-reasons', ExamRejectionReasons::class)
    ->name('platform.registration.exam.rejection-reasons')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Exam Rejection Reasons'), route('platform.registration.exam.rejection-reasons')));

//Platform > Registration > Periods > Exam
Route::screen('registration/periods/exam', ExamRegistrationPeriodListScreen::class)
    ->name('platform.registration.periods.exam');

//Platform > Registration > Periods > NSIN
Route::screen('registration/periods/nsin', NsinRegistrationPeriodListScreen::class)
    ->name('platform.registration.periods.nsin');

//Platform > Biometric > Verification
Route::screen('biometric/student_verification', StudentVerificationListScreen::class)
    ->name('platform.biometric.verification');

//Platform > Biometruc > Enrollment
Route::screen('biometric/enrollment', StudentEnrollmentListScreen::class)
    ->name('platform.biometric.enrollment');

//Platform > Biometruc > Access
Route::screen('biometric/access', StudentAccessLogListScreen::class)
    ->name('platform.biometric.access');

// Platform > System > Finance > Accounts
Route::screen('accounts/{account}/transactions', InstitutionTransactionListScreen::class)
    ->name('platform.systems.finance.institution.transactions')
    ->breadcrumbs(fn(Trail $trail, $account) => $trail
        ->parent('platform.systems.finance.accounts')
        ->push(optional($account->institution)->institution_name, route('platform.systems.finance.institution.transactions', $account)));

Route::screen('finance/accounts', AccountListScreen::class)
    ->name('platform.systems.finance.accounts')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Accounts'), route('platform.systems.finance.accounts')));

Route::screen('finance/transactions/pending', PendingTransactionListScreen::class)
    ->name('platform.systems.finance.pending')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Pending Transactions'), route('platform.systems.finance.pending')));

Route::screen('finance/transactions', TransactionListScreen::class)
    ->name('platform.systems.finance.complete')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Institution Transactions'), route('platform.systems.finance.complete')));

// Platform  > Reports > Packing List
Route::screen('reports/packing_list', PackingListReportScreen::class)
    ->name('platform.reports.packing_list');

// Platform  > Reports > Registration > NSIN
Route::screen('reports/nsin_registration', NSINRegistrationReportScreen::class)
    ->name('platform.reports.nsin_registration');

// Platform  > Reports > Registration > Exam
Route::screen('reports/exam_registration', ExamRegistrationReportScreen::class)
    ->name('platform.reports.exam_registration');

// Platform > Assessment > Marks
Route::screen('assessment/marks', AddStudentAssessmentFormScreen::class)
    ->name('platform.assessment.marks');

// Platform > Assessment > List
Route::screen('assessment', ContinuousAssessmentListScreen::class)
    ->name('platform.assessment.list');

// Platform > Student Research
Route::screen('student_research', StudentResearchListScreen::class)
    ->name('platform.student_research');

// Platform > System > Settings
Route::screen('settings', AppSettingListScreen::class)
    ->name('platform.systems.settings');
