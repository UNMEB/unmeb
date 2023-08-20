<?php

use App\Http\Controllers\ChartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\WidgetsController;
use App\Http\Controllers\SetLocaleController;
use App\Http\Controllers\ComponentsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SurchargeController;
use App\Http\Controllers\YearController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return to_route('login');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    // Dashboards
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard.index');

    // Administration
    Route::get('administration-courses', [CourseController::class, 'index'])->name('administration.courses');
    Route::get('administration-institutions', [InstitutionController::class, 'index'])->name('administration.institutions');
    Route::get('administration-papers', [PaperController::class, 'index'])->name('administration.papers');
    Route::get('administration-districts', [DistrictController::class, 'index'])->name('administration.districts');
    Route::get('administration-fees', [FeeController::class, 'index'])->name('administration.fees');
    Route::get('administration-years', [YearController::class, 'index'])->name('administration.years');

    // Exam Registration
    Route::get('exam-payments', [ExamController::class, 'payments'])->name('exam.payments');
    Route::get('exam-registrations', [ExamController::class, 'registrations'])->name('exam.registrations');
    Route::get('exam-approval', [ExamController::class, 'approval'])->name('exam.approval');
    Route::get('exam-approved', [ExamController::class, 'approved'])->name('exam.approved');
    Route::get('exam-rejected', [ExamController::class, 'rejected'])->name('exam.rejected');
    Route::get('exam-rejection-reasons', [ExamController::class, 'rejectionReasons'])->name('exam.rejection-reasons');

    // Student
    Route::get('payments/nsin', [PaymentController::class, 'nsinPayments'])->name('nsin-payments');
    Route::get('incomplete-nsin-registration', [StudentController::class, 'sinPaymentsIncomplete'])->name('incomplete-nsin-registration');

    // Comments
    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');

    // Surcharge
    Route::get('surcharge', [SurchargeController::class, 'index'])->name('surcharge.index');

    // Locale
    Route::get('setlocale/{locale}', SetLocaleController::class)->name('setlocale');

    // User
    Route::resource('users', UserController::class);
    // Permission
    Route::resource('permissions', PermissionController::class)->except(['show']);
    // Roles
    Route::resource('roles', RoleController::class);
    // Profiles
    Route::resource('profiles', ProfileController::class)->only(['index', 'update'])->parameter('profiles', 'user');
    // Env
    Route::singleton('general-settings', GeneralSettingController::class);
    Route::post('general-settings-logo', [GeneralSettingController::class, 'logoUpdate'])->name('general-settings.logo');

    // Database Backup
    Route::resource('database-backups', DatabaseBackupController::class)->name('database-backups', 'index');
    Route::get('database-backups-download/{fileName}', [DatabaseBackupController::class, 'databaseBackupDownload'])->name('database-backups.download');
});