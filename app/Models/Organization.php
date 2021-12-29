<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends ModelAbstract
{
    use HasFactory;

    protected static function booted() {
        static::created(function($Organization) {
            Tracker::create([
                'organization_id' => $Organization->id,
                'pixel_id' => 'ID-' . uniqid(),
            ]);
        });
    }

    // =========================================================================
    // Getters.
    // =========================================================================

    public function getTracker() {
        return $this->hasMany(Tracker::class)->first();
    }


    // =========================================================================
    // Relations.
    // =========================================================================

    public function Users() {
        return $this->belongsToMany(User::class);
    }

    public function Invites() {
        return $this->hasMany(Invite::class);
    }

    public function Funnels() {
        return $this->hasMany(Funnel::class);
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function addUser(User $User, string $role) {
        if ( ! in_array($role, User::ROLES)) {
            throw new \Exception("Role [$role] not legit.");
        }

        $this->Users()->attach($User->id, [
            'role' => $role,
        ]);
        return $this;
    }
}
