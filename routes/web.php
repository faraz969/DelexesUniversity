<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications/{id}', [App\Http\Controllers\AdminController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/status', [App\Http\Controllers\AdminController::class, 'updateStatus'])->name('applications.updateStatus');
    
    // Department Management
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
    
    // Program Management
    Route::resource('programs', App\Http\Controllers\Admin\ProgramController::class);
    
    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.resetPassword');
    
    // Form Type Management
    Route::resource('form-types', App\Http\Controllers\Admin\FormTypeController::class);
});

// HOD Routes
Route::middleware(['auth', 'role:hod'])->prefix('hod')->name('hod.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HODController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications/{application}', [App\Http\Controllers\HODController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/approve', [App\Http\Controllers\HODController::class, 'approveApplication'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [App\Http\Controllers\HODController::class, 'rejectApplication'])->name('applications.reject');
});

// President Routes
Route::middleware(['auth', 'role:president'])->prefix('president')->name('president.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\PresidentController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications/{application}', [App\Http\Controllers\PresidentController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/approve', [App\Http\Controllers\PresidentController::class, 'approveApplication'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [App\Http\Controllers\PresidentController::class, 'rejectApplication'])->name('applications.reject');
});

// Registrar Routes
Route::middleware(['auth', 'role:registrar'])->prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\RegistrarController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications/{application}', [App\Http\Controllers\RegistrarController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/approve', [App\Http\Controllers\RegistrarController::class, 'approveApplication'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [App\Http\Controllers\RegistrarController::class, 'rejectApplication'])->name('applications.reject');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Public admission form
Route::get('/admission', function () {
    $departments = \App\Models\Department::where('is_active', true)
        ->with(['activePrograms' => function($query) {
            $query->orderBy('sort_order');
        }])
        ->orderBy('sort_order')
        ->get();
    return view('admission.form', compact('departments'));
})->name('admission.form');

// Public registration (buy form)
Route::get('/registration', [RegistrationController::class, 'show'])->name('registration.create');
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');

// Payment routes
Route::post('/payment/initiate', [App\Http\Controllers\PaymentController::class, 'initiatePayment'])->name('payment.initiate');
Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancelled', [App\Http\Controllers\PaymentController::class, 'paymentCancelled'])->name('payment.cancelled');
Route::post('/payment/ipn', [App\Http\Controllers\PaymentController::class, 'handleIpn'])->name('payment.ipn');

// Portal (user)
Route::middleware(['auth'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/application', [DashboardController::class, 'applicationForm'])->name('application');
    Route::post('/application/save', [DashboardController::class, 'applicationSave'])->name('application.save');
    Route::post('/application/submit', [DashboardController::class, 'applicationSubmit'])->name('application.submit');
    Route::get('/application/print', [DashboardController::class, 'applicationPrint'])->name('application.print');
    Route::get('/results', [DashboardController::class, 'results'])->name('results');
});
