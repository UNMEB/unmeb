<?php

declare(strict_types=1);

use App\Orchid\Screens\Administration\Course\CourseListScreen;
use App\Orchid\Screens\Administration\District\DistrictListScreen;
use App\Orchid\Screens\Administration\Fee\FeeListScreen;
use App\Orchid\Screens\Administration\Institution\InstitutionListScreen;
use App\Orchid\Screens\Administration\Paper\PaperListScreen;
use App\Orchid\Screens\Administration\Surcharge\SurchargeFeeListScreen;
use App\Orchid\Screens\Administration\Surcharge\SurchargeListScreen;
use App\Orchid\Screens\Administration\Years\YearListScreen;
use App\Orchid\Screens\AssessmentScreen;
use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\ExamRegistrationPeriodScreen;
use App\Orchid\Screens\InstitutionCourseAssignScreen;
use App\Orchid\Screens\NSINAcceptedScreen;
use App\Orchid\Screens\NSINIncompleteScreen;
use App\Orchid\Screens\NSINRegistrationPeriodScreen;
use App\Orchid\Screens\NSINRejectedReasonsScreen;
use App\Orchid\Screens\NSINRejectedScreen;
use App\Orchid\Screens\NSINVerifyBookScreen;
use App\Orchid\Screens\NSINVerifyScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Registration\NSIN\NSINPaymentScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Staff\StaffListScreen;
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
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

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

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

//Route::screen('idea', Idea::class, 'platform.screens.idea');


Route::screen('staff', StaffListScreen::class)->name('platform.administration.staff');
Route::screen('student', StudentListScreen::class)->name('platform.administration.student');

Route::screen('districts', DistrictListScreen::class)->name('platform.administration.districts');

// Route::screen('institutions', InstitutionListScreen::class)->name('platform.administration.institutions');
// Route::screen('institution/assign', InstitutionCourseAssignScreen::class)->name('platform.administration.institutions');


Route::screen('registration/nsin/payments', NSINPaymentScreen::class)->name('platform.registration.nsin.payments');
Route::screen('registration/nsin/incomplete', NSINIncompleteScreen::class)->name('platform.registration.nsin.incomplete');
Route::screen('registration/nsin/verify', NSINVerifyScreen::class)->name('platform.registration.nsin.verify');
Route::screen('registration/nsin/accepted', NSINAcceptedScreen::class)->name('platform.registration.nsin.accepted');
Route::screen('registration/nsin/rejected', NSINRejectedScreen::class)->name('platform.registration.nsin.rejected');
Route::screen('registration/nsin/reasons', NSINRejectedReasonsScreen::class)->name('platform.registration.nsin.reasons');
Route::screen('registration/nsin/verify_books', NSINVerifyBookScreen::class)->name('platform.registration.nsin.verify_books');


Route::screen('registration/periods/nsin', NSINRegistrationPeriodScreen::class)->name('platform.registration.period.nsin');
Route::screen('registration/periods/exam', ExamRegistrationPeriodScreen::class)->name('platform.registration.period.exam');

Route::screen('institutions/{institution}/assign', InstitutionCourseAssignScreen::class)
    ->name('platform.administration.institutions.assign');

Route::screen('institutions', InstitutionListScreen::class)
    ->name('platform.administration.institutions');

Route::screen('courses', CourseListScreen::class)->name('platform.administration.courses');
Route::screen('papers', PaperListScreen::class)->name('platform.administration.papers');
Route::screen('surcharge/list', SurchargeListScreen::class)->name('platform.administration.surcharge.list');
Route::screen('surcharge/fees', SurchargeFeeListScreen::class)->name('platform.administration.surcharge.fees');
Route::screen('years', YearListScreen::class)->name('platform.administration.years');

Route::screen('assessment', AssessmentScreen::class)->name('platform.assessment.continuous');
