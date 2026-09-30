<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HrdManager\ProfileController as HrdProfileController;
use App\Http\Controllers\Karyawan\ProfileController as KaryawanProfileController;
use App\Http\Controllers\OaOcManager\ProfileController as OaOcProfileController;
use App\Http\Controllers\ProductManager\ProfileController as ProductManagerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    $profileControllers = [
        UserRole::ADMIN->value => AdminProfileController::class,
        UserRole::PRODUCT_MANAGER->value => ProductManagerProfileController::class,
        UserRole::HRD_MANAGER->value => HrdProfileController::class,
        UserRole::OA_OC_MANAGER->value => OaOcProfileController::class,
        UserRole::KARYAWAN->value => KaryawanProfileController::class,
    ];

    foreach (UserRole::cases() as $role) {
        $profileController = $profileControllers[$role->value];

        Route::prefix($role->value)
            ->middleware('role:'.$role->value)
            ->name($role->value.'.')
            ->group(function () use ($role, $profileController) {
                Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
                Route::get('/profile', [$profileController, 'show'])->name('profile');
                Route::put('/profile', [$profileController, 'update'])->name('profile.update');
                Route::put('/profile/password', [$profileController, 'updatePassword'])->name('profile.password');
                Route::get('/profile/signature', [$profileController, 'signature'])->name('profile.signature');
                Route::post('/profile/signature', [$profileController, 'storeSignature'])->name('profile.signature.store');

                if ($role === UserRole::ADMIN) {
                    Route::resource('users', UserController::class)->except(['show', 'destroy']);
                    Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);
                    Route::resource('positions', PositionController::class)->except(['show', 'destroy']);
                }
            });
    }
});
