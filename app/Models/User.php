<?php

namespace App\Models;

use App\Mail\WelcomeMail;
use App\Models\Organization;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'flag_admin' => 'boolean',
    ];

    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_VIEWER = 'viewer';

    const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_EDITOR,
        self::ROLE_VIEWER,
    ];


    // =========================================================================
    // Lifecycle event handlers.
    // =========================================================================

    protected static function booted() {
        static::created( function($User) {
            Mail::to($User->email)->send(new WelcomeMail($User));
        });
    }


    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organizations() {
        return $this->belongsToMany(Organization::class);
    }
}
