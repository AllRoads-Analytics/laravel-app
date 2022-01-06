<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invite;
use App\Models\Organization;
use Illuminate\Validation\Rule;
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
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $email = $Request->input('email');

        Invite::updateOrCreate([
            'organization_id' => $Organization->id,
            'email' => $email,
            'role' => $Request->input('role'),
        ]);

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "Invite email sent to $email.",
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

    public function edit_user(Request $Request, Organization $Organization, User $User) {
        $this->authorize('manage', $Organization);

        $Request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $User->Organizations()->updateExistingPivot($Organization->id, [
            'role' => $Request->input('role'),
        ]);

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "User [$User->name] updated.",
        ]);

        return redirect()->route('organizations.show', $Organization->id);
    }

    public function remove_invite(Request $Request, Organization $Organization, Invite $Invite) {
        $this->authorize('manage', $Organization);

        $email = $Invite->email;

        $Invite->delete();

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "Invite to [$email] removed.",
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

        $already_belongs = false;

        if ($User = auth()->user()) {
            $Request->session()->pull('invite_code');
            $already_belongs = $Invite->Organization->Users()->where('id', $User->id)->count()
                ? true : false;
        } else {
            $Request->session()->put('invite_code', $invite_code);
        }

        return view('_pages.invites.accept', [
            'Organization' => $Invite->Organization,
            'already_belongs' => $already_belongs,
        ]);
    }

    public function post_accept_invite(Request $Request, $invite_code) {
        $Invite = Invite::where('code', $invite_code)->first();
        $User = auth()->user();

        if ( ! $Invite) {
            return abort(403, 'Invite not found.');
        }

        $Organization = $Invite->Organization;

        if ( ! $Organization->Users()->where('id', $User->id)->count()) {
            $Organization->Users()->attach($User, [
                'role' => $Invite->role,
            ]);
        }

        $Invite->delete();

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "You have successfully joined $Organization->name.",
        ]);

        return redirect()->route('home');
    }
}
