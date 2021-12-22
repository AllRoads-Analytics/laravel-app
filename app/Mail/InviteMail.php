<?php

namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Invite */
    protected $Invite;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invite $Invite)
    {
        $this->Invite = $Invite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.invite', [
            'Invite' => $this->Invite,
        ])->subject('Pathfinder Analytics Invite');
    }
}
