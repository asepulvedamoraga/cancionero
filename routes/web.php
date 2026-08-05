<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\CatalogController as AdminCatalogController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\PublicSite\PublicPresentationController;
use App\Http\Controllers\PublicSite\PublicRepertoireController;
use App\Http\Controllers\PublicSite\PublicRepertoireDownloadController;
use App\Http\Controllers\PublicSite\PublicRepertoireFileController;
use App\Http\Controllers\RepertoireController;
use App\Http\Controllers\RepertoireExportController;
use App\Http\Controllers\RepertoireSongController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\SongFileController;
use App\Http\Controllers\SongToneController;
use Illuminate\Support\Facades\Route;

Route::prefix('r')->name('public.repertoires.')->group(function () {
    Route::get('/{repertoire:slug}', PublicRepertoireController::class)->name('show');
    Route::get('/{repertoire:slug}/presentation', PublicPresentationController::class)->name('presentation');
    Route::get('/{repertoire:slug}/files/{song}/{file}', PublicRepertoireFileController::class)->name('files.show');
    Route::get('/{repertoire:slug}/download', PublicRepertoireDownloadController::class)->middleware('throttle:10,1')->name('download');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', EmailVerificationNotificationController::class)->middleware('throttle:6,1')->name('verification.send');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/settings', AdminSettingsController::class)->name('settings');
        Route::get('/settings/catalogs/{catalog}', [AdminCatalogController::class, 'index'])->name('catalogs.index');
        Route::post('/settings/catalogs/{catalog}', [AdminCatalogController::class, 'store'])->name('catalogs.store');
        Route::put('/settings/catalogs/{catalog}/{item}', [AdminCatalogController::class, 'update'])->whereNumber('item')->name('catalogs.update');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

    Route::middleware('verified')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/songs/archived', [SongController::class, 'archived'])->name('songs.archived');
        Route::get('/songs/suggestions', [SongController::class, 'suggestions'])->middleware('throttle:60,1')->name('songs.suggestions');
        Route::put('/songs/{song}/restore', [SongController::class, 'restore'])->name('songs.restore');
        Route::delete('/songs/{song}/force', [SongController::class, 'forceDestroy'])->name('songs.force-destroy');
        Route::get('/songs/{song}/read', [SongController::class, 'read'])->name('songs.read');
        Route::resource('songs', SongController::class);
        Route::get('/songs/{song}/files/{file}', [SongFileController::class, 'show'])->name('songs.files.show');
        Route::get('/songs/{song}/files/{file}/preview', [SongFileController::class, 'preview'])->name('songs.files.preview');
        Route::get('/songs/{song}/files/{file}/download', [SongFileController::class, 'download'])->name('songs.files.download');
        Route::put('/songs/{song}/files/{file}/replace', [SongFileController::class, 'replace'])->name('songs.files.replace');
        Route::delete('/songs/{song}/files/{file}', [SongFileController::class, 'destroy'])->name('songs.files.destroy');
        Route::put('/songs/{song}/files/reorder', [SongFileController::class, 'reorder'])->name('songs.files.reorder');
        Route::post('/songs/{song}/tones', [SongToneController::class, 'store'])->name('songs.tones.store');
        Route::put('/songs/{song}/tones/{tone}/default', [SongToneController::class, 'makeDefault'])->name('songs.tones.default');
        Route::delete('/songs/{song}/tones/{tone}', [SongToneController::class, 'destroy'])->name('songs.tones.destroy');
        Route::post('/repertoires/{repertoire}/export', RepertoireExportController::class)->name('repertoires.export');
        Route::get('/repertoires/trashed', [RepertoireController::class, 'trashed'])->name('repertoires.trashed');
        Route::put('/repertoires/{repertoire}/restore', [RepertoireController::class, 'restore'])->whereNumber('repertoire')->name('repertoires.restore');
        Route::delete('/repertoires/{repertoire}/force', [RepertoireController::class, 'forceDestroy'])->whereNumber('repertoire')->name('repertoires.force-destroy');
        Route::get('/repertoires/{repertoire}/presentation', PresentationController::class)->name('repertoires.presentation');
        Route::post('/repertoires/{repertoire}/duplicate', [RepertoireController::class, 'duplicate'])->name('repertoires.duplicate');
        Route::post('/repertoires/{repertoire}/songs', [RepertoireSongController::class, 'store'])->name('repertoires.songs.store');
        Route::put('/repertoires/{repertoire}/songs/reorder', [RepertoireSongController::class, 'reorder'])->name('repertoires.songs.reorder');
        Route::put('/repertoires/{repertoire}/songs/{song}', [RepertoireSongController::class, 'update'])->name('repertoires.songs.update');
        Route::delete('/repertoires/{repertoire}/songs/{song}', [RepertoireSongController::class, 'destroy'])->name('repertoires.songs.destroy');
        Route::resource('repertoires', RepertoireController::class);
    });
});
