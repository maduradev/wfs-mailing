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
use App\Http\Controllers\Shared\LetterAttachmentController;
use App\Http\Controllers\Shared\LetterController;
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
    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::get('/letters/create/{type}', [LetterController::class, 'create'])->name('letters.create');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
    Route::get('/letters/{letter}', [LetterController::class, 'show'])->name('letters.show');
    Route::get('/letters/{letter}/edit', [LetterController::class, 'edit'])->name('letters.edit');
    Route::put('/letters/{letter}', [LetterController::class, 'update'])->name('letters.update');
    Route::post('/letters/{letter}/submit', [LetterController::class, 'submit'])->name('letters.submit');
    Route::post('/letters/{letter}/attachments', [LetterController::class, 'storeAttachment'])->name('letters.attachments.store');
    Route::get('/letters/attachments/{attachment}', [LetterAttachmentController::class, 'download'])->name('letters.attachments.download');
    Route::get('/letters/{letter}/preview.pdf', [LetterController::class, 'preview'])->name('letters.preview');

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
