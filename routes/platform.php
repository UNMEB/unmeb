<?php

declare(strict_types=1);

use App\Orchid\Screens\Administration\Course\AssignPaperListScreen;
use App\Orchid\Screens\Administration\Course\CourseListScreen;
use App\Orchid\Screens\Administration\Course\CoursePaperListScreen;
use App\Orchid\Screens\DemoReportScreen;
use App\Orchid\Screens\Administration\District\DistrictListScreen;
use App\Orchid\Screens\Administration\Institution\AssignCourseListScreen;
use App\Orchid\Screens\Administration\Institution\InstitutionCourseList;
use App\Orchid\Screens\Administration\Institution\InstitutionListScreen;
use App\Orchid\Screens\Administration\Paper\PaperListScreen;
use App\Orchid\Screens\Administration\Year\YearListScreen;
use App\Orchid\Screens\Assessment\PracticalAssessmentList;
use App\Orchid\Screens\Assessment\TheoryAssessmentList;
use App\Orchid\Screens\Biometric\AttendanceLogScreen;
use App\Orchid\Screens\Biometric\AttendanceReportScreen;
use App\Orchid\Screens\Biometric\EnrollmentListScreen;
use App\Orchid\Screens\ContinuousAssessmentScreen;
use App\Orchid\Screens\Finance\Account\AccountListScreen;
use App\Orchid\Screens\Finance\Account\CreditListScreen;
use App\Orchid\Screens\Finance\Account\InstitutionTransactionListScreen;
use App\Orchid\Screens\Finance\Account\PendingTransactionListScreen;
use App\Orchid\Screens\Finance\Account\TransactionListScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Registration\Exam\ApproveExamRegistrationListScreen;
use App\Orchid\Screens\Registration\Exam\ExamRegistrationListScreen;
use App\Orchid\Screens\Registration\Periods\ExamRegistrationPeriodListScreen;
use App\Orchid\Screens\Registration\Periods\StudentRegistrationPeriodListScreen;
use App\Orchid\Screens\Registration\Student\ApproveStudentRegistrationListScreen;
use App\Orchid\Screens\Registration\Student\StudentRegistrationListScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Student\StudentListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

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
Route::screen('/dashboard', PlatformScreen::class)
->name('platform.dashboard');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Platform > System > Administration > Years
Route::screen('years', YearListScreen::class)
    ->name('platform.systems.administration.years')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
->push(__('Years'), route('platform.systems.administration.years')));

// Platform > System > Administration > Districts
Route::screen('districts', DistrictListScreen::class)
    ->name('platform.systems.administration.districts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Institutions'), route('platform.systems.administration.districts')));

// Platform > System > Administration > Institution > Assign
Route::screen('institutions/{institution}/assign', AssignCourseListScreen::class)
    ->name('platform.systems.administration.institutions.assign')
    ->breadcrumbs(fn (Trail $trail, $institution) => $trail
        ->parent('platform.systems.administration.institutions')
        ->push($institution->name, route('platform.systems.administration.institutions.assign', $institution)));

// Platform > System > Administration > Institution > Courses
Route::screen('institutions/{institution}/courses', InstitutionCourseList::class)
    ->name('platform.systems.administration.institutions.courses')
    ->breadcrumbs(fn (Trail $trail, $institution) => $trail
        ->parent('platform.systems.administration.institutions')
        ->push($institution->name, route('platform.systems.administration.institutions.courses', $institution)));

// Platform > System > Administration > Institutions
Route::screen('institutions', InstitutionListScreen::class)
->name('platform.systems.administration.institutions')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Institutions'), route('platform.systems.administration.institutions')));

// Platform > System > Administration > Courses > Assign
Route::screen('courses/{course}/assign', AssignPaperListScreen::class)
    ->name('platform.systems.administration.courses.assign')
    ->breadcrumbs(fn (Trail $trail, $course) => $trail
        ->parent('platform.systems.administration.courses')
        ->push($course->name, route('platform.systems.administration.courses.assign', $course)));

// Platform > System > Administration > Course > Papers
Route::screen('courses/{course}/papers', CoursePaperListScreen::class)
    ->name('platform.systems.administration.courses.papers')
    ->breadcrumbs(fn (Trail $trail, $course) => $trail
        ->parent('platform.systems.administration.courses')
        ->push($course->name, route('platform.systems.administration.courses.papers', $course)));

// Platform > System > Administration > Courses
Route::screen('courses', CourseListScreen::class)
    ->name('platform.systems.administration.courses')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Courses'), route('platform.systems.administration.courses')));


// Platform > System > Administration > Papers
Route::screen('papers', PaperListScreen::class)
    ->name('platform.systems.administration.papers')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Papers'), route('platform.systems.administration.papers')));


// Platform > System > Registration > Students > Approve
Route::screen('registration/students/approve', ApproveStudentRegistrationListScreen::class)
    ->name('platform.systems.registration.students.approve');

// Platform > System > Registration > Students
Route::screen('registration/students', StudentRegistrationListScreen::class)
    ->name('platform.systems.registration.students');


// Platform > System > Registration > Exams
Route::screen('registration/exams/approve', ApproveExamRegistrationListScreen::class)
    ->name('platform.systems.registration.exams.approve');

// Platform > System > Registration > Exams
Route::screen('registration/exams', ExamRegistrationListScreen::class)
    ->name('platform.systems.registration.exams');

        // Platform > System > Registration > Periods > Student
Route::screen('registration/periods/students', StudentRegistrationPeriodListScreen::class)
    ->name('platform.systems.registration.periods.students')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Student Registration Periods'), route('platform.systems.registration.periods.students')));

// Platform > System > Registration > Periods > Exam
Route::screen('registration/periods/exams', ExamRegistrationPeriodListScreen::class)
    ->name('platform.systems.registration.periods.exams')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
->push(__('Exam Registration Periods'), route('platform.systems.registration.periods.exams')));


// Platform > System > Finance > Accounts
Route::screen('finance/accounts', AccountListScreen::class)
    ->name('platform.systems.finance.accounts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Accounts'), route('platform.systems.finance.accounts')));

Route::screen('finance/transactions/pending', PendingTransactionListScreen::class)
    ->name('platform.systems.finance.pending')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
->push(__('Pending Transactions'), route('platform.systems.finance.pending')));

Route::screen('finance/transactions', TransactionListScreen::class)
    ->name('platform.systems.finance.complete')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
->push(__('Institution Transactions'), route('platform.systems.finance.complete')));


// Platform > System > Finance > Accounts
Route::screen('accounts/{institution}/transactions', InstitutionTransactionListScreen::class)
    ->name('platform.systems.finance.institution.transactions')
    ->breadcrumbs(fn (Trail $trail, $institution) => $trail
        ->parent('platform.systems.finance.accounts')
        ->push($institution->name, route('platform.systems.finance.institution.transactions', $institution)));

// Platform > System > Reports
Route::screen('reports', DemoReportScreen::class);


// Platform > System > Continuous Assessment > Theory
Route::screen('continuous-assessment/theory', TheoryAssessmentList::class)
    ->name('platform.systems.continuous-assessment.theory')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Theory Assessment'), route('platform.systems.continuous-assessment.theory')));


// Platform > System > Continuous Assessment > Practical
Route::screen('continuous-assessment/practical', PracticalAssessmentList::class)
    ->name('platform.systems.continuous-assessment.practical')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Practical Assessment'), route('platform.systems.continuous-assessment.practical')));


// Platform > System > Biometric > Access Log
Route::screen('biometric/enrollment', EnrollmentListScreen::class)
    ->name('platform.system.biometrics.enrollment');

        // Platform > System > Biometric > Access Log
Route::screen('biometric/access', AttendanceLogScreen::class)
->name('platform.system.biometrics.access');

// Platform > System > Biometric > Access Log
Route::screen('biometric/report', AttendanceReportScreen::class)
->name('platform.system.biometrics.report');
