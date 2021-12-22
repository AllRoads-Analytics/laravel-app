<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Invite extends ModelAbstract
{
    use HasFactory;

    protected static function booted() {
        static::creating( function($Invite) {
            $Invite->code = Str::uuid();
        });
    }

    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function getAcceptRoute() {
        return route('organizations.invites.get_accept', ['invite_code' => $this->code]);
    }
}
