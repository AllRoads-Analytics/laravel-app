<?php

namespace App\Models;

use App\Mail\InviteMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Invite extends ModelAbstract
{
    use HasFactory;

    protected static function booted() {
        static::creating( function($Invite) {
            $Invite->code = Str::uuid();
        });

        static::created( function($Invite) {
            $Invite->sendEmail();
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

    public function sendEmail() {
        Mail::to($this->email)->send(new InviteMail($this));
        return $this;
    }
}
