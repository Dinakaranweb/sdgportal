<?php

use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EditorController;
use App\Http\Controllers\Admin\GoalController;
use App\Http\Controllers\Admin\GoalPageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Site\GoalController as PublicGoalController;
use App\Http\Controllers\Site\GoalPageController as PublicPageController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public portal
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', SearchController::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/goals', [PublicGoalController::class, 'index'])->name('goals.index');
Route::get('/goals/{goal:slug}', [PublicGoalController::class, 'show'])->name('goals.show');

// Sub menus and questions resolve at any depth, so the slug trail stays in the
// URL and can be shared with other portals as a direct deep link.
Route::get('/goals/{goal:slug}/{path}', PublicPageController::class)
    ->where('path', '[A-Za-z0-9\-_/]+')
    ->name('goals.page');

/*
|--------------------------------------------------------------------------
| Control panel
|--------------------------------------------------------------------------
*/

Route::prefix('control')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [SessionController::class, 'create'])->name('login');
        Route::post('login', [SessionController::class, 'store'])->name('login.attempt');
    });

    Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::post('goals/reorder', [GoalController::class, 'reorder'])->name('goals.reorder');
        Route::post('goals/{goal}/duplicate', [GoalController::class, 'duplicate'])->name('goals.duplicate');
        Route::resource('goals', GoalController::class);

        Route::prefix('goals/{goal}')->group(function () {
            Route::post('pages/reorder', [GoalPageController::class, 'reorder'])->name('pages.reorder');
            Route::get('pages', [GoalPageController::class, 'index'])->name('pages.index');
            Route::get('pages/create', [GoalPageController::class, 'create'])->name('pages.create');
            Route::post('pages', [GoalPageController::class, 'store'])->name('pages.store');
            Route::get('pages/{page}/edit', [GoalPageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}', [GoalPageController::class, 'update'])->name('pages.update');
            Route::delete('pages/{page}', [GoalPageController::class, 'destroy'])->name('pages.destroy');
            Route::post('pages/{page}/duplicate', [GoalPageController::class, 'duplicate'])->name('pages.duplicate');
        });

        // Blocks attach to either a goal or one of its pages.
        Route::prefix('blocks/{owner_type}/{owner_id}')->name('blocks.')->group(function () {
            Route::get('/', [BlockController::class, 'index'])->name('index');
            Route::get('create', [BlockController::class, 'create'])->name('create');
            Route::post('/', [BlockController::class, 'store'])->name('store');
            Route::post('reorder', [BlockController::class, 'reorder'])->name('reorder');
            Route::get('{block}/edit', [BlockController::class, 'edit'])->name('edit');
            Route::put('{block}', [BlockController::class, 'update'])->name('update');
            Route::delete('{block}', [BlockController::class, 'destroy'])->name('destroy');
            Route::post('{block}/duplicate', [BlockController::class, 'duplicate'])->name('duplicate');
            Route::post('{block}/toggle', [BlockController::class, 'toggle'])->name('toggle');
        });

        Route::get('settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');

        Route::post('menu/reorder', [MenuController::class, 'reorder'])->name('menu.reorder');
        Route::get('menu/{menu?}', [MenuController::class, 'index'])->name('menu.index');
        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
        Route::put('menu/{item}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('menu/{item}', [MenuController::class, 'destroy'])->name('menu.destroy');

        Route::post('editor/upload', [EditorController::class, 'upload'])->name('editor.upload');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::resource('users', UserController::class)->except('show');
    });
});
