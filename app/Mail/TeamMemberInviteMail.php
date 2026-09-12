<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamMemberInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $password, public bool $isNewInvite)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isNewInvite
                ? 'Запрошення до команди ' . config('app.name')
                : 'Ваш пароль оновлено',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-member-invite',
        );
    }
}
