<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\VerifToken;
use Carbon\Carbon;

class RegisMail extends Mailable
{
    use Queueable, SerializesModels;
    public $content;
    private $tokenData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id, $nama)
    {
        $token = \Illuminate\Support\Str::random(32);

        $this->tokenData = VerifToken::create([
            'user_id' => $user_id,
            'token' => $token,
            'expired_at' => Carbon::now()->addMinutes(30),
        ]);

        $this->tokenData->save();

        $this->content = [
            'nama' => $nama,
            'token' => $token,
        ];
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Verifikasi Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'verif.email_content',
            // data: [
            //     'token' => $this->tokenData->token,
            // ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
