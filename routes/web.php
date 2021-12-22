<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PathfinderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationUserController;

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


// =============================================================================
// Organization:
// =============================================================================

// https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller
Route::resource('organizations', OrganizationController::class);

Route::post('/organizations/{organization}/invites/create', [
    OrganizationUserController::class, 'create_invite'
])->name('organizations.invites.create');

Route::post('/organizations/{organization}/users/{user}/remove', [
    OrganizationUserController::class, 'remove_user'
])->name('organizations.users.remove');

Route::post('/organizations/{organization}/users/{user}/edit', [
    OrganizationUserController::class, 'edit_user'
])->name('organizations.users.edit');

Route::post('/organizations/{organization}/invites/{invite}/remove', [
    OrganizationUserController::class, 'remove_invite'
])->name('organizations.invites.remove');

Route::get('/accept-invite/{invite_code}', [
    OrganizationUserController::class, 'get_accept_invite'
])->name('organizations.invites.get_accept');

Route::post('/accept-invite/{invite_code}', [
    OrganizationUserController::class, 'post_accept_invite'
])->name('organizations.invites.post_accept')->middleware('auth');;


// =============================================================================
// Pathfinder:
// =============================================================================

Route::get('/pathfinder/{tracker:pixel_id}', [
    PathfinderController::class, 'get_tracker'
])->name('pathfinder.tracker');

Route::get('/pathfinder/{tracker:pixel_id}/{host}', [
    PathfinderController::class, 'get_tracker_host'
])->name('pathfinder.tracker.host');

Route::get('/pathfinder/ajax/{tracker:pixel_id}/{host}/next_pages', [
    PathfinderController::class, 'ajax_get_next_pages'
])->name('pathfinder.ajax.get_next_pages');

Route::get('/pathfinder/ajax/{tracker:pixel_id}/{host}/funnel', [
    PathfinderController::class, 'ajax_get_funnel'
])->name('pathfinder.ajax.get_funnel');
