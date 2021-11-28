<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PathfinderController;
use App\Http\Controllers\OrganizationController;

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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resources([
    'organizations' => OrganizationController::class,
]);

Route::get('/pathfinder/{tracker_pixel_id}', [
    PathfinderController::class, 'get_tracker'
])->name('pathfinder.tracker');

Route::get('/pathfinder/{tracker_pixel_id}/{host}', [
    PathfinderController::class, 'get_tracker_host'
])->name('pathfinder.tracker.host');

Route::get('/pathfinder/ajax/{tracker_pixel_id}/{host}/next_pages', [
    PathfinderController::class, 'ajax_get_next_pages'
])->name('pathfinder.ajax.get_next_pages');

Route::get('/pathfinder/ajax/{tracker_pixel_id}/{host}/funnel', [
    PathfinderController::class, 'ajax_get_funnel'
])->name('pathfinder.ajax.get_funnel');
