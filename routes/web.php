<?php

use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
    App\Http\Controllers\PathfinderController::class, 'get_tracker'
])->name('pathfinder.tracker');

Route::get('/pathfinder/{tracker_pixel_id}/{host}', [
    App\Http\Controllers\PathfinderController::class, 'get_tracker_host'
])->name('pathfinder.tracker.host');
