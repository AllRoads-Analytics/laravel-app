<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\BillingController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');


// =============================================================================
// Organization:
// =============================================================================

// https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller
Route::resource('organizations', OrganizationController::class);

Route::prefix('organizations/{organization}')->group(function() {
    Route::post('/invites/create', [
        OrganizationUserController::class, 'create_invite'
    ])->name('organizations.invites.create');

    Route::post('/users/{user}/remove', [
        OrganizationUserController::class, 'remove_user'
    ])->name('organizations.users.remove');

    Route::post('/users/{user}/edit', [
        OrganizationUserController::class, 'edit_user'
    ])->name('organizations.users.edit');

    Route::post('/invites/{invite}/remove', [
        OrganizationUserController::class, 'remove_invite'
    ])->name('organizations.invites.remove');


    // =========================================================================
    // Billing:
    // =========================================================================

    Route::middleware(['can:manage,organization'])->group(function () {
        // =====================================================================
        // Plan
        // =====================================================================
        Route::get('/select-plan', [
            BillingController::class, 'get_select_plan'
        ])->name('organizations.billing.get_select_plan');

        Route::post('/select-plan', [
            BillingController::class, 'post_select_plan'
        ])->name('organizations.billing.post_select_plan');

        // =====================================================================
        // Payment
        // =====================================================================
        Route::get('/payment', [
            BillingController::class, 'get_update_payment_method'
        ])->name('organizations.billing.get_update_payment_method');

        Route::post('/payment', [
            BillingController::class, 'post_update_payment_method'
        ])->name('organizations.billing.post_update_payment_method');
    });


    // =============================================================================
    // Funnel:
    // =============================================================================

    Route::get('/funnels', [
        FunnelController::class, 'index'
    ])->name('funnels.index');
});


// =============================================================================
// Invite accept.
// =============================================================================

Route::get('/accept-invite/{invite_code}', [
    OrganizationUserController::class, 'get_accept_invite'
])->name('organizations.invites.get_accept');

Route::post('/accept-invite/{invite_code}', [
    OrganizationUserController::class, 'post_accept_invite'
])->name('organizations.invites.post_accept')->middleware('auth');;


// =============================================================================
// Pathfinder:
// =============================================================================

Route::get('/funnel-explorer/{tracker:pixel_id}', [
    PathfinderController::class, 'get_tracker'
])->name('pathfinder.tracker');

Route::get('/funnel-explorer/ajax/{tracker:pixel_id}/filter_options', [
    PathfinderController::class, 'ajax_get_filter_options'
])->name('pathfinder.ajax.get_filter_options');

Route::get('/funnel-explorer/ajax/{tracker:pixel_id}/next_pages', [
    PathfinderController::class, 'ajax_get_next_pages'
])->name('pathfinder.ajax.get_next_pages');

Route::get('/funnel-explorer/ajax/{tracker:pixel_id}/funnel', [
    PathfinderController::class, 'ajax_get_funnel'
])->name('pathfinder.ajax.get_funnel');

Route::post('/funnel-explorer/ajax/{tracker:pixel_id}/funnel', [
    PathfinderController::class, 'ajax_post_funnel'
])->name('pathfinder.ajax.post_funnel');

Route::get('/funnel-explorer/ajax/saved_funnel_pages/{funnel}', [
    PathfinderController::class, 'ajax_get_saved_funnel_pages'
])->name('pathfinder.ajax.get_saved_funnel_pages');

Route::post('/funnel-explorer/ajax/funnel/{funnel}/delete', [
    PathfinderController::class, 'post_funnel_delete'
])->name('pathfinder.ajax.post_funnel_delete');



