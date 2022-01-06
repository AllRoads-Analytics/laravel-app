<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User */
    protected $User;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $User)
    {
        $this->User = $User;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.welcome', [
            'User' => $this->User,
        ])->subject('Welcome to AllRoads Analytics');
    }
}
