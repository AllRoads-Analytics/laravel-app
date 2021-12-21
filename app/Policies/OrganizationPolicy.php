<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    protected $Organization;

    public function viewAny(User $User) {
        return false;
    }

    public function view(User $User, Organization $Organization) {
        return $this->isAdmin($User, $Organization)
            || $this->isEditor($User, $Organization)
            || $this->isViewer($User, $Organization);
    }

    public function edit(User $User, Organization $Organization) {
        return $this->isAdmin($User, $Organization)
            || $this->isEditor($User, $Organization);
    }

    public function manage(User $User, Organization $Organization) {
        return $this->isAdmin($User, $Organization);
    }

    // =========================================================================
    // Protected functions.
    // =========================================================================

    protected function getUserOrganization($User, $Organization) {
        if ( ! isset($this->Organization)) {
            $this->Organization = $User->Organizations()
            ->withPivot('role')
            ->where('id', $Organization->id)
            ->first();
        }

        return $this->Organization;
    }

    protected function isAdmin($User, $Organization) {
        $Organization = $this->getUserOrganization($User, $Organization);
        return $Organization && $Organization->pivot->role === $User::ROLE_ADMIN;
    }

    protected function isEditor($User, $Organization) {
        $Organization = $this->getUserOrganization($User, $Organization);
        return $Organization && $Organization->pivot->role === $User::ROLE_EDITOR;
    }

    protected function isViewer($User, $Organization) {
        $Organization = $this->getUserOrganization($User, $Organization);
        return $Organization && $Organization->pivot->role === $User::ROLE_VIEWER;
    }
}
