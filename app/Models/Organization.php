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

    public function addUser(User $User) {
        $this->Users()->attach($User->id);
        return $this;
    }
}
