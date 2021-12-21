<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invite;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationUserController extends Controller
{
    // =========================================================================
    // Manage
    // =========================================================================

    public function create_invite(Request $Request, Organization $Organization) {
        $this->authorize('manage', $Organization);

        $Request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $Request->input('email');

        Invite::updateOrCreate([
            'organization_id' => $Organization->id,
            'email' => $email,
        ]);

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "$email invited.",
        ]);

        return redirect()->route('organizations.show', $Organization->id);
    }

    public function remove_user(Request $Request, Organization $Organization, User $User) {
        $this->authorize('manage', $Organization);

        $Organization->Users()->detach($User->id);

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "User [$User->name] removed.",
        ]);

        return redirect()->route('organizations.show', $Organization->id);
    }


    // =========================================================================
    // Accept
    // =========================================================================

    public function get_accept_invite(Request $Request, $invite_code) {
        $Invite = Invite::where('code', $invite_code)->first();

        if ( ! $Invite) {
            return abort(403, 'Invite not found.');
        }

        if ( ! auth()->user()) {
            $Request->session()->put('invite_code', $invite_code);
        } else {
            $Request->session()->pull('invite_code');
        }

        return view('_pages.invites.accept', [
            'Organization' => $Invite->Organization,
        ]);
    }

    public function post_accept_invite(Request $Request, $invite_code) {
        $Invite = Invite::where('code', $invite_code)->first();

        if ( ! $Invite) {
            return abort(403, 'Invite not found.');
        }

        $Organization = $Invite->Organization;

        $Organization->Users()->attach(auth()->user());
        $Invite->delete();

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "You have successfully joined $Organization->name.",
        ]);

        return redirect()->route('home');
    }
}
